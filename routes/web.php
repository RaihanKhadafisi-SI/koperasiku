<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Data anggota: admin dan pengurus
Route::middleware(['auth', 'role:admin,pengurus'])->group(function () {
    Route::resource('anggota', AnggotaController::class)
        ->parameters(['anggota' => 'anggota']);
});

// Simpanan: mencatat, mengedit, membatalkan (admin dan bendahara)
Route::middleware(['auth', 'role:admin,bendahara'])->group(function () {
    Route::resource('simpanan', SimpananController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->parameters(['simpanan' => 'simpanan']);
});

// Simpanan: melihat dan memverifikasi (admin, pengurus, bendahara)
Route::middleware(['auth', 'role:admin,pengurus,bendahara'])->group(function () {
    Route::resource('simpanan', SimpananController::class)
        ->only(['index', 'show'])
        ->parameters(['simpanan' => 'simpanan']);

    Route::post('/simpanan/{simpanan}/verifikasi', [SimpananController::class, 'verifikasi'])
        ->name('simpanan.verifikasi');
    Route::post('/simpanan/{simpanan}/tolak', [SimpananController::class, 'tolak'])
        ->name('simpanan.tolak');
});

// Manajemen user: hanya admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});

require __DIR__.'/auth.php';