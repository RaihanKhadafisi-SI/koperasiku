@extends('layouts.app')

@section('title', 'Tambah Anggota')
@section('page-title', 'Tambah Anggota')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Masukkan data anggota koperasi baru</h3>
    </div>
    <form action="{{ route('anggota.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @include('anggota._form')
        </div>
    </form>
</div>
@endsection