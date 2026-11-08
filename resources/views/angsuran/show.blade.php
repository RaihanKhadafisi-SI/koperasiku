@extends('layouts.app')

@section('title', 'Jadwal Angsuran')
@section('page-title', 'Jadwal Angsuran')

@section('content')
@php
    $bisaBayar = in_array(auth()->user()->role, ['admin', 'bendahara']);
    $totalAngsuran = $angsurans->count();
    $jumlahLunas = $angsurans->where('status', 'lunas')->count();
    $persen = $totalAngsuran > 0 ? round($jumlahLunas / $totalAngsuran * 100) : 0;
    $sisaTagihan = $angsurans->where('status', 'belum_bayar')->sum('total_tagihan');
@endphp

<div class="row">
    {{-- Informasi pinjaman --}}
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Informasi Pinjaman</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <th style="width: 40%;">Anggota</th>
                        <td>
                            {{ $pinjaman->anggota->nama ?? '-' }}
                            <small class="text-muted">({{ $pinjaman->anggota->no_anggota ?? '-' }})</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Pinjaman</th>
                        <td>Rp {{ number_format($pinjaman->jumlah_disetujui ?? $pinjaman->jumlah_pengajuan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Tenor</th>
                        <td>{{ $pinjaman->tenor_bulan }} Bulan</td>
                    </tr>
                    <tr>
                        <th>Bunga</th>
                        <td>{{ number_format($pinjaman->bunga_persen, 2, ',', '.') }}% per tahun</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($pinjaman->status === 'lunas')
                                <span class="badge badge-success">Lunas</span>
                            @else
                                <span class="badge badge-primary">Berjalan</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Progres pembayaran --}}
    <div class="col-md-6">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">Progres Pembayaran</h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1">
                    <span>{{ $jumlahLunas }} dari {{ $totalAngsuran }} angsuran lunas</span>
                    <strong>{{ $persen }}%</strong>
                </div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" role="progressbar"
                         style="width: {{ $persen }}%"></div>
                </div>
                <p class="mb-0">
                    Sisa tagihan:
                    <strong>Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Jadwal Angsuran</h3>
        <div class="card-tools">
            <a href="{{ route('angsuran.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card-body">
        @if($angsurans->isEmpty())
            <div class="text-center text-muted py-4">
                <p>Jadwal angsuran untuk pinjaman ini belum dibuat.</p>
                @if($bisaBayar)
                    <form action="{{ route('angsuran.generate', $pinjaman) }}" method="POST"
                          onsubmit="return confirm('Buat jadwal angsuran untuk pinjaman ini?')">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-calendar-plus mr-1"></i> Buat Jadwal
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 70px;">Ke-</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-right">Pokok</th>
                            <th class="text-right">Bunga</th>
                            <th class="text-right">Total Tagihan</th>
                            <th>Status</th>
                            <th>Tanggal Bayar</th>
                            @if($bisaBayar)
                                <th style="width: 120px;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($angsurans as $a)
                            <tr>
                                <td>{{ $a->angsuran_ke }}</td>
                                <td>{{ $a->jatuh_tempo->format('d/m/Y') }}</td>
                                <td class="text-right">Rp {{ number_format($a->pokok, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($a->bunga, 0, ',', '.') }}</td>
                                <td class="text-right"><strong>Rp {{ number_format($a->total_tagihan, 0, ',', '.') }}</strong></td>
                                <td>
                                    @if($a->status === 'lunas')
                                        <span class="badge badge-success">Lunas</span>
                                    @elseif($a->isTerlambat())
                                        <span class="badge badge-danger">Terlambat</span>
                                    @else
                                        <span class="badge badge-warning">Belum Bayar</span>
                                    @endif
                                </td>
                                <td>
                                    @if($a->tanggal_bayar)
                                        {{ $a->tanggal_bayar->format('d/m/Y') }}
                                        <br><small class="text-muted">{{ $a->pembayar->name ?? '' }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @if($bisaBayar)
                                    <td>
                                        @if($a->status === 'belum_bayar')
                                            <form action="{{ route('angsuran.bayar', $a) }}" method="POST"
                                                  onsubmit="return confirm('Catat pembayaran angsuran ke-{{ $a->angsuran_ke }}?')">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-money-bill-wave mr-1"></i> Bayar
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <th colspan="2" class="text-right">Total</th>
                            <th class="text-right">Rp {{ number_format($angsurans->sum('pokok'), 0, ',', '.') }}</th>
                            <th class="text-right">Rp {{ number_format($angsurans->sum('bunga'), 0, ',', '.') }}</th>
                            <th class="text-right">Rp {{ number_format($angsurans->sum('total_tagihan'), 0, ',', '.') }}</th>
                            <th colspan="{{ $bisaBayar ? 3 : 2 }}"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection