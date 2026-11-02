<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pinjamans', function (Blueprint $table) {
            $table->id();

            // Relasi anggota
            $table->foreignId('anggota_id')
                ->constrained('anggota')
                ->restrictOnDelete();

            // Informasi pengajuan
            $table->decimal('jumlah_pengajuan', 15, 2);
            $table->decimal('jumlah_disetujui', 15, 2)->nullable();
            $table->decimal('bunga_persen', 5, 2)->default(0);
            $table->unsignedSmallInteger('tenor_bulan');
            $table->text('tujuan');

            // Status pengajuan
            $table->string('status', 20)->default('diajukan');

            // Jejak persetujuan atau penolakan
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan_persetujuan')->nullable();

            // Informasi pencairan dan pelunasan
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('lunas_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjamans');
    }
};