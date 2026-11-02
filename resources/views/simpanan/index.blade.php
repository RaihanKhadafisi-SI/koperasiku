@extends('layouts.app')

@section('title', 'Data Simpanan')
@section('page-title', 'Data Simpanan')

@section('content')
@php
    $bisaMencatat = in_array(auth()->user()->role, ['admin', 'bendahara']);
    $badgeStatus = [
        'menunggu' => 'warning',
        'terverifikasi' => 'success',
        'ditolak' => 'danger',
        'dibatalkan' => 'secondary',
    ];
@endphp

<div class="row">
    @foreach([
        ['label' => 'Simpanan Pokok', 'value' => $rekap->total_pokok, 'bg' => 'bg-info', 'icon' => 'fa-coins'],
        ['label' => 'Simpanan Wajib', 'value' => $rekap->total_wajib, 'bg' => 'bg-success', 'icon' => 'fa-calendar-check'],
        ['label' => 'Simpanan Sukarela', 'value' => $rekap->total_sukarela, 'bg' => 'bg-warning', 'icon' => 'fa-hand-holding-heart'],
        ['label' => 'Total Terverifikasi', 'value' => $rekap->total_simpanan, 'bg' => 'bg-primary', 'icon' => 'fa-wallet'],
    ] as $card)
        <div class="col-lg-3 col-6">
            <div class="small-box {{ $card['bg'] }}">
                <div class="inner">
                    <h3 style="font-size: 1.5rem">
                        Rp {{ number_format((float) $card['value'], 0, ',', '.') }}
                    </h3>
                    <p>{{ $card['label'] }}</p>
                </div>
                <div class="icon"><i class="fas {{ $card['icon'] }}"></i></div>
            </div>
        </div>
    @endforeach
</div>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title">Transaksi simpanan anggota koperasi</h3>
        @if($bisaMencatat)
            <a href="{{ route('simpanan.create') }}" class="btn btn-primary btn-sm ml-auto">
                <i class="fas fa-plus"></i> Tambah Simpanan
            </a>
        @endif
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('simpanan.index') }}" class="row mb-3">
            <div class="col-lg-3 col-md-6 mb-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari kode atau nama anggota"
                       value="{{ request('search') }}">
            </div>
            <div class="col-lg-2 col-md-6 mb-2">
                <select name="jenis" class="form-control">
                    <option value="">Semua jenis</option>
                    @foreach(['pokok', 'wajib', 'sukarela'] as $jenis)
                        <option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>
                            {{ ucfirst($jenis) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6 mb-2">
                <select name="status" class="form-control">
                    <option value="">Semua status</option>
                    @foreach(['menunggu', 'terverifikasi', 'ditolak', 'dibatalkan'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6 mb-2">
                <input type="date" name="dari" class="form-control"
                       aria-label="Tanggal awal" value="{{ request('dari') }}">
            </div>
            <div class="col-lg-2 col-md-6 mb-2">
                <input type="date" name="sampai" class="form-control"
                       aria-label="Tanggal akhir" value="{{ request('sampai') }}">
            </div>
            <div class="col-lg-1 col-md-6 mb-2">
                <button class="btn btn-primary btn-block" type="submit">Filter</button>
            </div>
            <div class="col-12">
                <a href="{{ route('simpanan.index') }}" class="btn btn-sm btn-secondary">
                    Reset filter
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>No.</th>
                        <th>Kode</th>
                        <th>Anggota</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th style="min-width: 160px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($simpanan as $item)
                        <tr>
                            <td>{{ $simpanan->firstItem() + $loop->index }}</td>
                            <td>{{ $item->kode_transaksi }}</td>
                            <td>
                                {{ $item->anggota->nama }}<br>
                                <small class="text-muted">{{ $item->anggota->no_anggota }}</small>
                            </td>
                            <td>{{ ucfirst($item->jenis) }}</td>
                            <td class="text-nowrap">
                                Rp {{ number_format((float) $item->nominal, 0, ',', '.') }}
                            </td>
                            <td>{{ $item->tanggal_transaksi->format('d-m-Y') }}</td>
                            <td>
                                <span class="badge badge-{{ $badgeStatus[$item->status] ?? 'secondary' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('simpanan.show', $item) }}"
                                   class="btn btn-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($bisaMencatat && $item->status === 'menunggu')
                                    <a href="{{ route('simpanan.edit', $item) }}"
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('simpanan.destroy', $item) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Batalkan transaksi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" title="Batalkan">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                Belum ada transaksi simpanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
            <small class="text-muted">Total {{ $simpanan->total() }} transaksi</small>
            {{ $simpanan->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection