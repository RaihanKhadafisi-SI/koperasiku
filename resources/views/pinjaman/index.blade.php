@extends('layouts.app')

@section('title', 'Manajemen Pinjaman')
@section('page-title', 'Manajemen Pinjaman')

@section('content')
@php
    $colors = [
        'diajukan' => 'warning',
        'disetujui' => 'info',
        'ditolak' => 'danger',
        'dicairkan' => 'primary',
        'lunas' => 'success',
        'dibatalkan' => 'secondary',
    ];
@endphp

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalPengajuan }}</h3>
                <p>Total Pengajuan</p>
            </div>
            <div class="icon"><i class="fas fa-file-alt"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $menunggu }}</h3>
                <p>Menunggu Persetujuan</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $disetujui }}</h3>
                <p>Disetujui</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $dicairkan }}</h3>
                <p>Sudah Dicairkan</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title">Daftar Pengajuan Pinjaman</h3>
        <a href="{{ route('pinjaman.create') }}" class="btn btn-primary btn-sm ml-auto">
            <i class="fas fa-plus"></i> Tambah Pinjaman
        </a>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('pinjaman.index') }}" class="row mb-3">
            <div class="col-md-5 mb-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari nama atau nomor anggota..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-4 mb-2">
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    @foreach(array_keys($colors) as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <button class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="{{ route('pinjaman.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Anggota</th>
                        <th>Jumlah Pengajuan</th>
                        <th>Jumlah Disetujui</th>
                        <th>Tenor</th>
                        <th>Bunga</th>
                        <th>Status</th>
                        <th style="min-width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pinjamans as $pinjaman)
                        <tr>
                            <td>{{ $pinjamans->firstItem() + $loop->index }}</td>
                            <td>
                                <strong>{{ $pinjaman->anggota->nama }}</strong><br>
                                <small class="text-muted">{{ $pinjaman->anggota->no_anggota }}</small>
                            </td>
                            <td class="text-nowrap">
                                Rp {{ number_format((float) $pinjaman->jumlah_pengajuan, 0, ',', '.') }}
                            </td>
                            <td class="text-nowrap">
                                @if($pinjaman->jumlah_disetujui !== null)
                                    Rp {{ number_format((float) $pinjaman->jumlah_disetujui, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $pinjaman->tenor_bulan }} Bulan</td>
                            <td>{{ $pinjaman->bunga_persen }}%</td>
                            <td>
                                <span class="badge badge-{{ $colors[$pinjaman->status] ?? 'secondary' }}">
                                    {{ ucfirst($pinjaman->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('pinjaman.show', $pinjaman) }}"
                                   class="btn btn-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($pinjaman->status === 'diajukan')
                                    <a href="{{ route('pinjaman.edit', $pinjaman) }}"
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('pinjaman.destroy', $pinjaman) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus pengajuan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                Belum ada data pinjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $pinjamans->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection