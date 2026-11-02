@extends('layouts.app')

@section('title', 'Edit Simpanan')
@section('page-title', 'Edit Simpanan')

@section('content')
<div class="alert alert-warning">
    Hanya transaksi berstatus menunggu yang dapat diedit.
    Kode transaksi: <strong>{{ $simpanan->kode_transaksi }}</strong>
</div>

<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title">Perbarui transaksi simpanan</h3>
    </div>
    <form action="{{ route('simpanan.update', $simpanan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('simpanan._form')
        </div>
    </form>
</div>
@endsection