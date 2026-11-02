<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Pinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PinjamanController extends Controller
{
    private function rules(?Pinjaman $pinjaman = null): array
    {
        return [
            'anggota_id' => [
                'required',
                'integer',
                Rule::exists('anggota', 'id')->where(function ($q) use ($pinjaman) {
                    $q->where('status', 'aktif');

                    if ($pinjaman) {
                        $q->orWhere('id', $pinjaman->anggota_id);
                    }
                }),
            ],
            'jumlah_pengajuan' => ['required', 'numeric', 'min:1', 'max:9999999999999'],
            'bunga_persen' => ['required', 'numeric', 'min:0', 'max:100'],
            'tenor_bulan' => ['required', 'integer', 'min:1', 'max:120'],
            'tujuan' => ['required', 'string', 'max:2000'],
        ];
    }

    public function index(Request $request)
    {
        $query = Pinjaman::with(['anggota', 'pengurus'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_anggota', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pinjamans = $query->paginate(10)->withQueryString();

        $totalPengajuan = Pinjaman::count();
        $menunggu = Pinjaman::where('status', 'diajukan')->count();
        $disetujui = Pinjaman::where('status', 'disetujui')->count();
        $dicairkan = Pinjaman::where('status', 'dicairkan')->count();

        return view('pinjaman.index', compact(
            'pinjamans',
            'totalPengajuan',
            'menunggu',
            'disetujui',
            'dicairkan'
        ));
    }

    public function create()
    {
        $anggotas = Anggota::where('status', 'aktif')->orderBy('nama')->get();

        return view('pinjaman.create', compact('anggotas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $validated['status'] = 'diajukan';

        Pinjaman::create($validated);

        return redirect()
            ->route('pinjaman.index')
            ->with('success', 'Pengajuan pinjaman berhasil disimpan.');
    }

    public function show(Pinjaman $pinjaman)
    {
        $pinjaman->load(['anggota', 'pengurus']);

        return view('pinjaman.show', compact('pinjaman'));
    }

    public function edit(Pinjaman $pinjaman)
    {
        abort_unless($pinjaman->status === 'diajukan', 403,
            'Hanya pengajuan yang belum diproses yang dapat diubah.');

        $anggotas = Anggota::where('status', 'aktif')
            ->orWhere('id', $pinjaman->anggota_id)
            ->orderBy('nama')
            ->get();

        return view('pinjaman.edit', compact('pinjaman', 'anggotas'));
    }

    public function update(Request $request, Pinjaman $pinjaman)
    {
        abort_unless($pinjaman->status === 'diajukan', 403,
            'Pengajuan yang sudah diproses tidak dapat diubah.');

        $validated = $request->validate($this->rules($pinjaman));

        $pinjaman->update($validated);

        return redirect()
            ->route('pinjaman.index')
            ->with('success', 'Data pinjaman berhasil diperbarui.');
    }

    public function destroy(Pinjaman $pinjaman)
    {
        abort_unless($pinjaman->status === 'diajukan', 403,
            'Pinjaman yang sudah diproses tidak dapat dihapus.');

        $pinjaman->delete();

        return redirect()
            ->route('pinjaman.index')
            ->with('success', 'Pengajuan pinjaman berhasil dihapus.');
    }

    /**
     * Menyetujui pengajuan.
     */
    public function approve(Request $request, Pinjaman $pinjaman)
    {
        $validated = $request->validate([
            'jumlah_disetujui' => ['required', 'numeric', 'min:1'],
            'catatan_persetujuan' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($pinjaman, $validated, $request) {
            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data->status !== 'diajukan') {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            if ((float) $validated['jumlah_disetujui'] > (float) $data->jumlah_pengajuan) {
                throw ValidationException::withMessages([
                    'jumlah_disetujui' =>
                        'Jumlah yang disetujui tidak boleh melebihi pengajuan.',
                ]);
            }

            $data->update([
                'jumlah_disetujui' => $validated['jumlah_disetujui'],
                'status' => 'disetujui',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'catatan_persetujuan' => $validated['catatan_persetujuan'] ?? null,
            ]);
        });

        return back()->with('success', 'Pinjaman berhasil disetujui.');
    }

    /**
     * Menolak pengajuan.
     */
    public function reject(Request $request, Pinjaman $pinjaman)
    {
        $validated = $request->validate([
            'catatan_persetujuan' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($pinjaman, $validated, $request) {
            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data->status !== 'diajukan') {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            $data->update([
                'status' => 'ditolak',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'catatan_persetujuan' => $validated['catatan_persetujuan'],
            ]);
        });

        return back()->with('success', 'Pengajuan pinjaman ditolak.');
    }

    /**
     * Mencairkan pinjaman yang telah disetujui.
     */
    public function disburse(Pinjaman $pinjaman)
    {
        DB::transaction(function () use ($pinjaman) {
            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data->status !== 'disetujui') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pinjaman yang disetujui dapat dicairkan.',
                ]);
            }

            $data->update([
                'status' => 'dicairkan',
                'disbursed_at' => now(),
            ]);
        });

        return back()->with('success', 'Pinjaman berhasil dicairkan.');
    }

    /**
     * Mencatat pelunasan.
     */
    public function markPaid(Pinjaman $pinjaman)
    {
        DB::transaction(function () use ($pinjaman) {
            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data->status !== 'dicairkan') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pinjaman yang sudah dicairkan dapat dilunasi.',
                ]);
            }

            $data->update([
                'status' => 'lunas',
                'lunas_at' => now(),
            ]);
        });

        return back()->with('success', 'Pinjaman berhasil dilunasi.');
    }

    /**
     * Membatalkan pengajuan yang belum dicairkan.
     */
    public function cancel(Pinjaman $pinjaman)
    {
        DB::transaction(function () use ($pinjaman) {
            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($data->status, ['diajukan', 'disetujui'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pinjaman ini tidak dapat dibatalkan.',
                ]);
            }

            $data->update(['status' => 'dibatalkan']);
        });

        return back()->with('success', 'Pinjaman berhasil dibatalkan.');
    }
}