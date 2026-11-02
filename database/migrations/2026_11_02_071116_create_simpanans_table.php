<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simpanan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('anggota_id')
                ->constrained('anggota')
                ->restrictOnDelete();

            $table->string('kode_transaksi', 40)->unique();

            $table->enum('jenis', ['pokok', 'wajib', 'sukarela']);

            $table->decimal('nominal', 15, 2);
            $table->date('tanggal_transaksi');

            $table->enum('status', [
                'menunggu',
                'terverifikasi',
                'ditolak',
                'dibatalkan',
            ])->default('menunggu');

            $table->text('keterangan')->nullable();

            $table->foreignId('dibuat_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('diverifikasi_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('diverifikasi_pada')->nullable();
            $table->text('alasan_penolakan')->nullable();

            $table->timestamps();

            $table->index(['anggota_id', 'tanggal_transaksi']);
            $table->index(['status', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simpanan');
    }
};