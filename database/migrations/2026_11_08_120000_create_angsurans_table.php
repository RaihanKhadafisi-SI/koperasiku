<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angsurans', function (Blueprint $table) {
            $table->id();

            // Relasi ke pinjaman
            $table->foreignId('pinjaman_id')
                ->constrained('pinjamans')
                ->restrictOnDelete();

            // Jadwal angsuran
            $table->unsignedSmallInteger('angsuran_ke');
            $table->date('jatuh_tempo');
            $table->decimal('pokok', 15, 2);
            $table->decimal('bunga', 15, 2);
            $table->decimal('total_tagihan', 15, 2);

            // Status pembayaran: belum_bayar atau lunas
            $table->string('status', 20)->default('belum_bayar');
            $table->date('tanggal_bayar')->nullable();
            $table->foreignId('dibayar_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Satu pinjaman tidak boleh punya dua angsuran dengan nomor yang sama
            $table->unique(['pinjaman_id', 'angsuran_ke']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angsurans');
    }
};