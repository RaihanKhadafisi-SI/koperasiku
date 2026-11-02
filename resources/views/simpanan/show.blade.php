@extends('layouts.app')

@section('title', 'Detail Simpanan')
@section('page-title', 'Detail Simpanan')

@section('content')
@php
    $badgeStatus = [
        'menunggu' => 'warning',
        'terverifikasi' => 'success',
        'ditolak' => 'danger',
        'dibatalkan' => 'secondary',
    ];
@endphp

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Informasi Transaksi {{ $simpanan->kode_transaksi }}</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th style="width: 230px">Kode Transaksi</th>
                <td>{{ $simpanan->kode_transaksi }}</td>
            </tr>
            <tr>
                <th>Anggota</th>
                <td>{{ $simpanan->anggota->nama }} ({{ $simpanan->anggota->no_anggota }})</td>
            </tr>
            <tr>
                <th>Jenis Simpanan</th>
                <td>{{ ucfirst($simpanan->jenis) }}</td>
            </tr>
            <tr>
                <th>Nominal</th>
                <td>
                    <strong>Rp {{ number_format((float) $simpanan->nominal, 0, ',', '.') }}</strong>
                </td>
            </tr>
            <tr>
                <th>Tanggal Transaksi</th>
                <td>{{ $simpanan->tanggal_transaksi->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <span class="badge badge-{{ $badgeStatus[$simpanan->status] ?? 'secondary' }}">
                        {{ ucfirst($simpanan->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Keterangan</th>
                <td>{{ $simpanan->keterangan ?: '-' }}</td>
            </tr>
            <tr>
                <th>Dibuat oleh</th>
                <td>{{ $simpanan->pembuat->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Diverifikasi oleh</th>
                <td>{{ $simpanan->verifikator->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Waktu verifikasi</th>
                <td>
                    {{ $simpanan->diverifikasi_pada
                        ? $simpanan->diverifikasi_pada->format('d-m-Y H:i')
                        : '-' }}
                </td>
            </tr>
            <tr>
                <th>Alasan penolakan</th>
                <td>{{ $simpanan->alasan_penolakan ?: '-' }}</td>
            </tr>
        </table>
    </div>
    <div class="card-footer">
        <a href="{{ route('simpanan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@if($simpanan->status === 'menunggu')
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Proses Verifikasi</h3>
        </div>
        <div class="card-body">
            <form method="POST"
                  action="{{ route('simpanan.verifikasi', $simpanan) }}"
                  onsubmit="return confirm('Verifikasi transaksi ini?')"
                  class="mb-4">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Verifikasi Simpanan
                </button>
            </form>

            <hr>

            <form method="POST" action="{{ route('simpanan.tolak', $simpanan) }}">
                @csrf
                <div class="form-group">
                    <label>Alasan penolakan</label>
                    <textarea name="alasan_penolakan" rows="2"
                              class="form-control @error('alasan_penolakan') is-invalid @enderror"
                              minlength="5" maxlength="1000"
                              required>{{ old('alasan_penolakan') }}</textarea>
                    @error('alasan_penolakan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Tolak transaksi ini?')">
                    <i class="fas fa-times"></i> Tolak Simpanan
                </button>
            </form>
        </div>
    </div>
@endif
@endsection