<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\Pinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AngsuranController extends Controller
{
    /**
     * Daftar pinjaman yang sudah dicairkan beserta progres angsurannya.
     */
    public function index(Request $request)
    {
        $query = Pinjaman::with('anggota')
            ->whereIn('status', ['dicairkan', 'lunas'])
            ->withCount([
                'angsuran',
                'angsuran as angsuran_lunas_count' => fn ($q) => $q->where('status', 'lunas'),
            ])
            ->withSum(
                ['angsuran as sisa_tagihan' => fn ($q) => $q->where('status', 'belum_bayar')],
                'total_tagihan'
            )
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_anggota', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->status, ['dicairkan', 'lunas'], true)) {
            $query->where('status', $request->status);
        }

        $pinjamans = $query->paginate(10)->withQueryString();

        // Kotak ringkasan di bagian atas
        $berjalan = Pinjaman::where('status', 'dicairkan')->count();
        $sisaTagihan = Angsuran::where('status', 'belum_bayar')->sum('total_tagihan');
        $totalTerbayar = Angsuran::where('status', 'lunas')->sum('total_tagihan');
        $terlambat = Angsuran::where('status', 'belum_bayar')
            ->whereDate('jatuh_tempo', '<', today())
            ->count();

        return view('angsuran.index', compact(
            'pinjamans',
            'berjalan',
            'sisaTagihan',
            'totalTerbayar',
            'terlambat'
        ));
    }

    /**
     * Jadwal angsuran satu pinjaman.
     */
    public function show(Pinjaman $pinjaman)
    {
        abort_unless(in_array($pinjaman->status, ['dicairkan', 'lunas'], true), 404);

        $pinjaman->load('anggota');

        $angsurans = $pinjaman->angsuran()
            ->with('pembayar')
            ->orderBy('angsuran_ke')
            ->get();

        return view('angsuran.show', compact('pinjaman', 'angsurans'));
    }

    /**
     * Membuat jadwal angsuran (bunga flat per tahun, dibagi rata per bulan).
     */
    public function generate(Pinjaman $pinjaman)
    {
        $hasil = DB::transaction(function () use ($pinjaman) {
            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($data->status, ['dicairkan', 'lunas'], true)) {
                return ['error', 'Jadwal hanya bisa dibuat untuk pinjaman yang sudah dicairkan.'];
            }

            if ($data->angsuran()->exists()) {
                return ['error', 'Jadwal angsuran untuk pinjaman ini sudah ada.'];
            }

            $pokokTotal = (float) ($data->jumlah_disetujui ?? $data->jumlah_pengajuan);
            $tenor = (int) $data->tenor_bulan;
            $bungaTotal = round($pokokTotal * ((float) $data->bunga_persen / 100) * ($tenor / 12), 2);

            $pokokBulan = round($pokokTotal / $tenor, 2);
            $bungaBulan = round($bungaTotal / $tenor, 2);

            $mulai = ($data->disbursed_at ?? now())->copy()->startOfDay();

            for ($i = 1; $i <= $tenor; $i++) {
                // Angsuran terakhir menyerap sisa pembulatan
                $pokok = $i === $tenor
                    ? round($pokokTotal - ($pokokBulan * ($tenor - 1)), 2)
                    : $pokokBulan;

                $bunga = $i === $tenor
                    ? round($bungaTotal - ($bungaBulan * ($tenor - 1)), 2)
                    : $bungaBulan;

                Angsuran::create([
                    'pinjaman_id' => $data->id,
                    'angsuran_ke' => $i,
                    'jatuh_tempo' => $mulai->copy()->addMonthsNoOverflow($i),
                    'pokok' => $pokok,
                    'bunga' => $bunga,
                    'total_tagihan' => round($pokok + $bunga, 2),
                    'status' => 'belum_bayar',
                ]);
            }

            return ['success', "Jadwal {$tenor} angsuran berhasil dibuat."];
        });

        return redirect()
            ->route('angsuran.show', $pinjaman)
            ->with($hasil[0], $hasil[1]);
    }

    /**
     * Mencatat pembayaran satu angsuran.
     */
    public function bayar(Request $request, Angsuran $angsuran)
    {
        $hasil = DB::transaction(function () use ($angsuran, $request) {
            $data = Angsuran::whereKey($angsuran->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data->status === 'lunas') {
                return ['error', 'Angsuran ini sudah dibayar.'];
            }

            // Angsuran harus dibayar berurutan
            $adaYangBelumDibayar = Angsuran::where('pinjaman_id', $data->pinjaman_id)
                ->where('angsuran_ke', '<', $data->angsuran_ke)
                ->where('status', 'belum_bayar')
                ->exists();

            if ($adaYangBelumDibayar) {
                return ['error', 'Bayar angsuran sebelumnya terlebih dahulu.'];
            }

            $data->update([
                'status' => 'lunas',
                'tanggal_bayar' => today(),
                'dibayar_oleh' => $request->user()->id,
            ]);

            $sisa = Angsuran::where('pinjaman_id', $data->pinjaman_id)
                ->where('status', 'belum_bayar')
                ->count();

            if ($sisa === 0) {
                $pinjaman = Pinjaman::whereKey($data->pinjaman_id)
                    ->lockForUpdate()
                    ->first();

                if ($pinjaman && $pinjaman->status === 'dicairkan') {
                    $pinjaman->update([
                        'status' => 'lunas',
                        'lunas_at' => now(),
                    ]);
                }

                return ['success', "Angsuran ke-{$data->angsuran_ke} berhasil dibayar. Seluruh angsuran sudah lunas, pinjaman berstatus LUNAS."];
            }

            return ['success', "Angsuran ke-{$data->angsuran_ke} berhasil dibayar."];
        });

        return back()->with($hasil[0], $hasil[1]);
    }
}