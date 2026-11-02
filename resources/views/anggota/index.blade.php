@extends('layouts.app')

@section('title', 'Data Anggota')
@section('page-title', 'Data Anggota')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title">Kelola data anggota koperasi simpan pinjam</h3>
        <a href="{{ route('anggota.create') }}" class="btn btn-primary btn-sm ml-auto">
            <i class="fas fa-plus"></i> Tambah Anggota
        </a>
    </div>

    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Data belum dapat disimpan.</strong>
                Periksa kembali kolom yang ditandai pada formulir.
            </div>
        @endif

        <form method="GET" action="{{ route('anggota.index') }}" class="row mb-3">
            <div class="col-md-6 mb-2">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari nomor anggota, NIK, nama, atau HP..."
                       value="{{ $keyword }}">
            </div>
            <div class="col-md-6 mb-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="{{ route('anggota.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>No.</th>
                        <th>No. Anggota</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>No. HP</th>
                        <th>Tgl Bergabung</th>
                        <th>Status</th>
                        <th style="min-width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($anggota as $item)
                        <tr>
                            <td>{{ $anggota->firstItem() + $loop->index }}</td>
                            <td>{{ $item->no_anggota }}</td>
                            <td>{{ $item->nik }}</td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td>{{ $item->no_hp }}</td>
                            <td>{{ $item->tanggal_bergabung->format('d-m-Y') }}</td>
                            <td>
                                @if($item->status === 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('anggota.show', $item) }}"
                                   class="btn btn-info btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('anggota.edit', $item) }}"
                                   class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('anggota.destroy', $item) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus anggota ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                Data anggota belum tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
            <small class="text-muted">
                Menampilkan {{ $anggota->firstItem() ?? 0 }}–{{ $anggota->lastItem() ?? 0 }}
                dari {{ $anggota->total() }} anggota
            </small>
            {{ $anggota->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection