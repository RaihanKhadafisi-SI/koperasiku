@extends('layouts.app')

@section('title', 'Angsuran')
@section('page-title', 'Angsuran Pinjaman')

@section('content')
@php
    $bisaKelola = in_array(auth()->user()->role, ['admin', 'bendahara']);
@endphp

{{-- Kotak ringkasan --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $berjalan }}</h3>
                <p>Pinjaman Berjalan</p>
            </div>
            <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 style="font-size: 1.6rem;">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</h3>
                <p>Sisa Tagihan</p>
            </div>
            <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 style="font-size: 1.6rem;">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</h3>
                <p>Total Sudah Dibayar</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $terlambat }}</h3>
                <p>Angsuran Terlambat</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Daftar Pinjaman yang Dicairkan</h3>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('angsuran.index') }}" class="form-row mb-3">
            <div class="col-md-5 mb-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari nama atau nomor anggota..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3 mb-2">
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="dicairkan" {{ request('status') === 'dicairkan' ? 'selected' : '' }}>Berjalan</option>
                    <option value="lunas" {{ request('status') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-1"></i> Cari
                </button>
                <a href="{{ route('angsuran.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Anggota</th>
                        <th>Jumlah Pinjaman</th>
                        <th>Tenor</th>
                        <th>Terbayar</th>
                        <th>Sisa Tagihan</th>
                        <th>Status</th>
                        <th style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pinjamans as $p)
                        <tr>
                            <td>{{ $pinjamans->firstItem() + $loop->index }}</td>
                            <td>
                                <strong>{{ $p->anggota->nama ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $p->anggota->no_anggota ?? '' }}</small>
                            </td>
                            <td>Rp {{ number_format($p->jumlah_disetujui ?? $p->jumlah_pengajuan, 0, ',', '.') }}</td>
                            <td>{{ $p->tenor_bulan }} Bulan</td>
                            <td>
                                @if($p->angsuran_count > 0)
                                    {{ $p->angsuran_lunas_count }} / {{ $p->angsuran_count }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($p->angsuran_count > 0)
                                    Rp {{ number_format($p->sisa_tagihan ?? 0, 0, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($p->status === 'lunas')
                                    <span class="badge badge-success">Lunas</span>
                                @else
                                    <span class="badge badge-primary">Berjalan</span>
                                @endif
                            </td>
                            <td>
                                @if($p->angsuran_count > 0)
                                    <a href="{{ route('angsuran.show', $p) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-list mr-1"></i> Lihat Jadwal
                                    </a>
                                @elseif($bisaKelola)
                                    <form action="{{ route('angsuran.generate', $p) }}" method="POST"
                                          onsubmit="return confirm('Buat jadwal angsuran untuk pinjaman ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-calendar-plus mr-1"></i> Buat Jadwal
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">Belum ada jadwal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada pinjaman yang dicairkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($pinjamans->hasPages())
        <div class="card-footer">
            {{ $pinjamans->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection