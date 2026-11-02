@extends('layouts.app')

@section('title', 'Detail Anggota')
@section('page-title', 'Detail Anggota')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Informasi Anggota Koperasi</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th style="width: 220px">Nomor Anggota</th>
                <td>{{ $anggota->no_anggota }}</td>
            </tr>
            <tr>
                <th>NIK</th>
                <td>{{ $anggota->nik }}</td>
            </tr>
            <tr>
                <th>Nama Lengkap</th>
                <td>{{ $anggota->nama }}</td>
            </tr>
            <tr>
                <th>Jenis Kelamin</th>
                <td>{{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <th>Tempat, Tanggal Lahir</th>
                <td>
                    {{ $anggota->tempat_lahir ?? '-' }},
                    {{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('d-m-Y') : '-' }}
                </td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td>{{ $anggota->alamat }}</td>
            </tr>
            <tr>
                <th>Nomor HP</th>
                <td>{{ $anggota->no_hp }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $anggota->email ?? '-' }}</td>
            </tr>
            <tr>
                <th>Tanggal Bergabung</th>
                <td>{{ $anggota->tanggal_bergabung->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ ucfirst($anggota->status) }}</td>
            </tr>
        </table>
    </div>
    <div class="card-footer">
        <a href="{{ route('anggota.edit', $anggota) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('anggota.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>
@endsection