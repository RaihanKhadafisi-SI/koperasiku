@extends('layouts.app')

@section('title', 'Tambah Simpanan')
@section('page-title', 'Tambah Simpanan')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Transaksi baru akan berstatus menunggu verifikasi</h3>
    </div>
    <form action="{{ route('simpanan.store') }}" method="POST">
        @csrf
        <div class="card-body">
            @include('simpanan._form')
        </div>
    </form>
</div>
@endsection