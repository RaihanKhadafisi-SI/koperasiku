@extends('layouts.app')

@section('title', 'Edit Anggota')
@section('page-title', 'Edit Anggota')

@section('content')
<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title">Perbarui data {{ $anggota->nama }}</h3>
    </div>
    <form action="{{ route('anggota.update', $anggota) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('anggota._form')
        </div>
    </form>
</div>
@endsection