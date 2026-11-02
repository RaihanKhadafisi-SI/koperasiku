<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Simpanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SimpananController extends Controller
{
    /**
     * Filter pencarian, jenis, dan rentang tanggal.
     * Dipakai bersama oleh tabel dan rekap.
     */
    private function applyFilters($query, Request $request)
    {
        return $query
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = $request->search;

                $q->where(function ($sub) use ($keyword) {
                    $sub->where('kode_transaksi', 'like', "%{$keyword}%")
                        ->orWhereHas('anggota', function ($a) use ($keyword) {
                            $a->where('nama', 'like', "%{$keyword}%")
                              ->orWhere('no_anggota', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($request->filled('jenis'), function ($q) use ($request) {
                $q->where('jenis', $request->jenis);
            })
            ->when($request->filled('dari'), function ($q) use ($request) {
                $q->whereDate('tanggal_transaksi', '>=', $request->dari);
            })
            ->when($request->filled('sampai'), function ($q) use ($request) {
                $q->whereDate('tanggal_transaksi', '<=', $request->sampai);
            });
    }

    public function index(Request $request)
    {
        $simpanan = $this->applyFilters(
                Simpanan::with(['anggota', 'pembuat']),
                $request
            )
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Rekap hanya transaksi terverifikasi, tidak terpengaruh filter status.
        $rekap = $this->applyFilters(Simpanan::query(), $request)
            ->where('status', 'terverifikasi')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN jenis = 'pokok'
                    THEN nominal ELSE 0 END), 0) as total_pokok,
                COALESCE(SUM(CASE WHEN jenis = 'wajib'
                    THEN nominal ELSE 0 END), 0) as total_wajib,
                COALESCE(SUM(CASE WHEN jenis = 'sukarela'
                    THEN nominal ELSE 0 END), 0) as total_sukarela,
                COALESCE(SUM(nominal), 0) as total_simpanan
            ")
            ->first();

        return view('simpanan.index', compact('simpanan', 'rekap'));
    }

    public function create()
    {
        $anggota = Anggota::where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        return view('simpanan.create', compact('anggota'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anggota_id' => [
                'required',
                'integer',
                Rule::exists('anggota', 'id')
                    ->where(fn ($q) => $q->where('status', 'aktif')),
            ],
            'jenis' => ['required', Rule::in(['pokok', 'wajib', 'sukarela'])],
            'nominal' => ['required', 'numeric', 'gt:0', 'max:9999999999999'],
            'tanggal_transaksi' => ['required', 'date', 'before_or_equal:today'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        $simpanan = DB::transaction(function () use ($validated, $request) {
            return Simpanan::create([
                'anggota_id' => $validated['anggota_id'],
                'kode_transaksi' => 'SMP-' . now()->format('YmdHis')
                    . '-' . Str::upper(Str::random(5)),
                'jenis' => $validated['jenis'],
                'nominal' => $validated['nominal'],
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'status' => 'menunggu',
                'keterangan' => $validated['keterangan'] ?? null,
                'dibuat_oleh' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('simpanan.show', $simpanan)
            ->with('success', 'Transaksi simpanan berhasil dicatat dan menunggu verifikasi.');
    }

    public function show(Simpanan $simpanan)
    {
        $simpanan->load(['anggota', 'pembuat', 'verifikator']);

        return view('simpanan.show', compact('simpanan'));
    }

    public function edit(Simpanan $simpanan)
    {
        abort_unless($simpanan->status === 'menunggu', 403,
            'Hanya transaksi yang menunggu verifikasi yang dapat diedit.');

        $anggota = Anggota::where('status', 'aktif')
            ->orWhere('id', $simpanan->anggota_id)
            ->orderBy('nama')
            ->get();

        return view('simpanan.edit', compact('simpanan', 'anggota'));
    }

    public function update(Request $request, Simpanan $simpanan)
    {
        abort_unless($simpanan->status === 'menunggu', 403,
            'Transaksi yang sudah diproses tidak dapat diedit.');

        $validated = $request->validate([
            'anggota_id' => [
                'required',
                'integer',
                Rule::exists('anggota', 'id')
                    ->where(fn ($q) => $q->where('status', 'aktif')
                        ->orWhere('id', $simpanan->anggota_id)),
            ],
            'jenis' => ['required', Rule::in(['pokok', 'wajib', 'sukarela'])],
            'nominal' => ['required', 'numeric', 'gt:0', 'max:9999999999999'],
            'tanggal_transaksi' => ['required', 'date', 'before_or_equal:today'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($simpanan, $validated) {
            $locked = Simpanan::whereKey($simpanan->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($locked->status === 'menunggu', 403,
                'Status transaksi sudah berubah.');

            $locked->update($validated);
        });

        return redirect()
            ->route('simpanan.show', $simpanan)
            ->with('success', 'Transaksi simpanan berhasil diperbarui.');
    }

    /**
     * Tidak menghapus baris. Hanya mengubah status menjadi dibatalkan
     * agar jejak transaksi tetap tersedia.
     */
    public function destroy(Simpanan $simpanan)
    {
        DB::transaction(function () use ($simpanan) {
            $locked = Simpanan::whereKey($simpanan->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($locked->status === 'menunggu', 403,
                'Hanya transaksi menunggu yang dapat dibatalkan melalui menu ini.');

            $locked->update(['status' => 'dibatalkan']);
        });

        return redirect()
            ->route('simpanan.index')
            ->with('success', 'Transaksi menunggu berhasil dibatalkan.');
    }

    public function verifikasi(Request $request, Simpanan $simpanan)
    {
        DB::transaction(function () use ($simpanan, $request) {
            $transaksi = Simpanan::whereKey($simpanan->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($transaksi->status === 'menunggu', 422,
                'Transaksi sudah pernah diproses.');

            $transaksi->update([
                'status' => 'terverifikasi',
                'diverifikasi_oleh' => $request->user()->id,
                'diverifikasi_pada' => now(),
                'alasan_penolakan' => null,
            ]);
        });

        return redirect()
            ->route('simpanan.show', $simpanan)
            ->with('success', 'Transaksi simpanan berhasil diverifikasi.');
    }

    public function tolak(Request $request, Simpanan $simpanan)
    {
        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        DB::transaction(function () use ($simpanan, $validated, $request) {
            $transaksi = Simpanan::whereKey($simpanan->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($transaksi->status === 'menunggu', 422,
                'Transaksi sudah pernah diproses.');

            $transaksi->update([
                'status' => 'ditolak',
                'diverifikasi_oleh' => $request->user()->id,
                'diverifikasi_pada' => now(),
                'alasan_penolakan' => $validated['alasan_penolakan'],
            ]);
        });

        return redirect()
            ->route('simpanan.show', $simpanan)
            ->with('success', 'Transaksi simpanan ditolak.');
    }
}