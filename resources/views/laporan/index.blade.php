@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan Koperasi')

@push('styles')
<style>
    @media print {
        .main-sidebar, .main-header, .main-footer,
        .content-header, .no-print {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
            background: #fff !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
        .small-box {
            border: 1px solid #ddd;
            color: #000 !important;
            background: #fff !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $namaJenis = [
        'pokok' => 'Simpanan Pokok',
        'wajib' => 'Simpanan Wajib',
        'sukarela' => 'Simpanan Sukarela',
    ];

    $namaStatus = [
        'diajukan' => 'Diajukan',
        'disetujui' => 'Disetujui',
        'dicairkan' => 'Dicairkan',
        'lunas' => 'Lunas',
        'ditolak' => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
    ];

    $periode = $dari->format('d/m/Y') . ' s.d. ' . $sampai->format('d/m/Y');
@endphp

{{-- Judul khusus saat dicetak --}}
<div class="d-none d-print-block text-center mb-3">
    <h3 class="mb-0">Laporan KoperasiKu</h3>
    <p class="mb-0">Periode: {{ $periode }}</p>
    <hr>
</div>

@if($errors->any())
    <div class="alert alert-danger no-print">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

{{-- Filter tanggal --}}
<div class="card card-primary card-outline no-print">
    <div class="card-body">
        <form method="GET" action="{{ route('laporan.index') }}" class="form-row align-items-end">
            <div class="col-md-3 mb-2">
                <label for="dari" class="mb-1">Dari tanggal</label>
                <input type="date" id="dari" name="dari" class="form-control"
                       value="{{ $dari->toDateString() }}">
            </div>
            <div class="col-md-3 mb-2">
                <label for="sampai" class="mb-1">Sampai tanggal</label>
                <input type="date" id="sampai" name="sampai" class="form-control"
                       value="{{ $sampai->toDateString() }}">
            </div>
            <div class="col-md-6 mb-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter mr-1"></i> Tampilkan
                </button>
                <a href="{{ route('laporan.index') }}" class="btn btn-secondary">Reset</a>
                <button type="button" class="btn btn-success" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Cetak
                </button>
            </div>
        </form>
    </div>
</div>

<p class="text-muted">Periode laporan: <strong>{{ $periode }}</strong></p>

{{-- Kotak ringkasan --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 style="font-size: 1.3rem;">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</h3>
                <p>Simpanan Terverifikasi</p>
            </div>
            <div class="icon"><i class="fas fa-wallet"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3 style="font-size: 1.3rem;">Rp {{ number_format($totalPinjamanCair, 0, ',', '.') }}</h3>
                <p>Pinjaman Dicairkan</p>
            </div>
            <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 style="font-size: 1.3rem;">Rp {{ number_format($angsuranDibayar->total ?? 0, 0, ',', '.') }}</h3>
                <p>Angsuran Diterima</p>
            </div>
            <div class="icon"><i class="fas fa-money-check-alt"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 style="font-size: 1.3rem;">Rp {{ number_format($sisaPiutang, 0, ',', '.') }}</h3>
                <p>Sisa Piutang Saat Ini</p>
            </div>
            <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Rekap simpanan --}}
    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Rekap Simpanan (Terverifikasi)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Jenis</th>
                            <th class="text-center">Transaksi</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($namaJenis as $kode => $label)
                            @php $baris = $simpananPerJenis->get($kode); @endphp
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="text-center">{{ $baris->jumlah ?? 0 }}</td>
                                <td class="text-right">Rp {{ number_format($baris->total ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th>Total</th>
                            <th class="text-center">{{ $simpananPerJenis->sum('jumlah') }}</th>
                            <th class="text-right">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Rekap pinjaman --}}
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Rekap Pinjaman (Berdasarkan Tanggal Pengajuan)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Status</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-right">Diajukan</th>
                            <th class="text-right">Disetujui</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($namaStatus as $kode => $label)
                            @php $baris = $pinjamanPerStatus->get($kode); @endphp
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="text-center">{{ $baris->jumlah ?? 0 }}</td>
                                <td class="text-right">Rp {{ number_format($baris->total_pengajuan ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($baris->total_disetujui ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th>Total</th>
                            <th class="text-center">{{ $pinjamanPerStatus->sum('jumlah') }}</th>
                            <th class="text-right">Rp {{ number_format($pinjamanPerStatus->sum('total_pengajuan'), 0, ',', '.') }}</th>
                            <th class="text-right">Rp {{ number_format($pinjamanPerStatus->sum('total_disetujui'), 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Rekap angsuran --}}
<div class="card card-success card-outline">
    <div class="card-header">
        <h3 class="card-title">Rekap Angsuran</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-sm mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Keterangan</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Angsuran dibayar pada periode ini (total)</td>
                    <td class="text-center">{{ $angsuranDibayar->jumlah ?? 0 }}</td>
                    <td class="text-right">Rp {{ number_format($angsuranDibayar->total ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="pl-4">&bull; Bagian pokok</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($angsuranDibayar->pokok ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="pl-4">&bull; Bagian bunga</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($angsuranDibayar->bunga ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Angsuran terlambat saat ini (belum dibayar, lewat jatuh tempo)</td>
                    <td class="text-center">{{ $jumlahTerlambat }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Sisa piutang seluruh angsuran yang belum dibayar</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($sisaPiutang, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Rekap per anggota --}}
<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title">Rekap per Anggota</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>No. Anggota</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th class="text-right">Total Simpanan</th>
                        <th class="text-right">Total Pinjaman Cair</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($anggotas as $a)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $a->no_anggota }}</td>
                            <td>{{ $a->nama }}</td>
                            <td>{{ ucfirst($a->status) }}</td>
                            <td class="text-right">Rp {{ number_format($a->total_simpanan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($a->total_pinjaman ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada data anggota.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <th colspan="4" class="text-right">Total</th>
                        <th class="text-right">Rp {{ number_format($anggotas->sum('total_simpanan'), 0, ',', '.') }}</th>
                        <th class="text-right">Rp {{ number_format($anggotas->sum('total_pinjaman'), 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection