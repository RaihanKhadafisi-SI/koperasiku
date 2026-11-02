@extends('layouts.app')

@section('title', 'Tambah Pinjaman')
@section('page-title', 'Tambah Pinjaman')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Form Pengajuan Pinjaman</h3>
    </div>
    <form action="{{ route('pinjaman.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @include('pinjaman._form')
        </div>
    </form>
</div>
@endsection