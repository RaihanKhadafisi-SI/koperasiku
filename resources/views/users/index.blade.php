@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Kelola akun dan hak akses KoperasiKu</h3>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm ml-auto">
            <i class="fas fa-plus"></i> Tambah User
        </a>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="row mb-3">
            <div class="col-md-5 mb-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari nama atau email"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3 mb-2">
                <select name="role" class="form-control">
                    <option value="">Semua role</option>
                    @foreach(['admin', 'pengurus', 'bendahara', 'anggota'] as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>
                            {{ ucfirst($role) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <button class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="min-width: 190px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $item)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>
                                <span class="badge badge-primary">{{ ucfirst($item->role) }}</span>
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('users.show', $item) }}"
                                   class="btn btn-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $item) }}"
                                   class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(auth()->id() !== $item->id)
                                    <form action="{{ route('users.destroy', $item) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus user ini?')">
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
                            <td colspan="6" class="text-center py-4">Belum ada data user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection