@extends('layouts.app')

@section('title', 'Edit Pinjaman')
@section('page-title', 'Edit Pinjaman')

@section('content')
<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title">Edit Pengajuan Pinjaman</h3>
    </div>
    <form action="{{ route('pinjaman.update', $pinjaman) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('pinjaman._form')
        </div>
    </form>
</div>
@endsection