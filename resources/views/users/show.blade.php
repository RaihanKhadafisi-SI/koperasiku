@extends('layouts.app')

@section('title', 'Detail User')
@section('page-title', 'Detail User')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Informasi Akun</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th style="width: 200px">Nama</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <th>Role</th>
                <td>{{ ucfirst($user->role) }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</td>
            </tr>
            <tr>
                <th>Dibuat</th>
                <td>{{ $user->created_at?->format('d-m-Y H:i') }}</td>
            </tr>
            <tr>
                <th>Terakhir diperbarui</th>
                <td>{{ $user->updated_at?->format('d-m-Y H:i') }}</td>
            </tr>
        </table>
    </div>
    <div class="card-footer">
        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit User
        </a>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>
@endsection