@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title">Perbarui akun {{ $user->name }}</h3>
    </div>
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('users._form')
        </div>
    </form>
</div>
@endsection