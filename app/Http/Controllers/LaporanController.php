<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\Anggota;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ], [
            'sampai.after_or_equal' => 'Tanggal "sampai" tidak boleh sebelum tanggal "dari".',
        ]);

        // Default: awal tahun ini sampai hari ini
        $dari = $request->filled('dari')
            ? Carbon::parse($request->dari)->startOfDay()
            : now()->startOfYear();

        $sampai = $request->filled('sampai')
            ? Carbon::parse($request->sampai)->endOfDay()
            : now()->endOfDay();

        $tglDari = $dari->toDateString();
        $tglSampai = $sampai->toDateString();

        // 1. Simpanan terverifikasi per jenis (berdasarkan tanggal transaksi)
        $simpananPerJenis = Simpanan::select(
                'jenis',
                DB::raw('COUNT(*) as jumlah'),
                DB::raw('SUM(nominal) as total')
            )
            ->where('status', 'terverifikasi')
            ->whereBetween('tanggal_transaksi', [$tglDari, $tglSampai])
            ->groupBy('jenis')
            ->get()
            ->keyBy('jenis');

        // 2. Pinjaman per status (berdasarkan tanggal pengajuan)
        $pinjamanPerStatus = Pinjaman::select(
                'status',
                DB::raw('COUNT(*) as jumlah'),
                DB::raw('SUM(jumlah_pengajuan) as total_pengajuan'),
                DB::raw('SUM(COALESCE(jumlah_disetujui, 0)) as total_disetujui')
            )
            ->whereBetween('created_at', [$dari, $sampai])
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // 3. Angsuran yang dibayar pada periode ini
        $angsuranDibayar = Angsuran::where('status', 'lunas')
            ->whereBetween('tanggal_bayar', [$tglDari, $tglSampai])
            ->selectRaw('COUNT(*) as jumlah,
                         COALESCE(SUM(pokok), 0) as pokok,
                         COALESCE(SUM(bunga), 0) as bunga,
                         COALESCE(SUM(total_tagihan), 0) as total')
            ->first();

        // 4. Piutang saat ini (tidak terpengaruh filter tanggal)
        $sisaPiutang = Angsuran::where('status', 'belum_bayar')->sum('total_tagihan');
        $jumlahTerlambat = Angsuran::where('status', 'belum_bayar')
            ->whereDate('jatuh_tempo', '<', today())
            ->count();

        // 5. Rekap per anggota
        $anggotas = Anggota::withSum(
                ['simpanan as total_simpanan' => fn ($q) => $q
                    ->where('status', 'terverifikasi')
                    ->whereBetween('tanggal_transaksi', [$tglDari, $tglSampai])],
                'nominal'
            )
            ->withSum(
                ['pinjaman as total_pinjaman' => fn ($q) => $q
                    ->whereIn('status', ['dicairkan', 'lunas'])
                    ->whereBetween('created_at', [$dari, $sampai])],
                'jumlah_disetujui'
            )
            ->orderBy('nama')
            ->get();

        $totalSimpanan = $simpananPerJenis->sum('total');

        // Pinjaman yang sudah dicairkan = status dicairkan + lunas
        $totalPinjamanCair = $pinjamanPerStatus
            ->filter(fn ($baris) => in_array($baris->status, ['dicairkan', 'lunas'], true))
            ->sum('total_disetujui');

        return view('laporan.index', compact(
            'dari',
            'sampai',
            'simpananPerJenis',
            'pinjamanPerStatus',
            'angsuranDibayar',
            'sisaPiutang',
            'jumlahTerlambat',
            'anggotas',
            'totalSimpanan',
            'totalPinjamanCair'
        ));
    }
}