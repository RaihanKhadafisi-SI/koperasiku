<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pinjaman extends Model
{
    protected $table = 'pinjamans';

    protected $fillable = [
        'anggota_id',
        'jumlah_pengajuan',
        'jumlah_disetujui',
        'bunga_persen',
        'tenor_bulan',
        'tujuan',
        'status',
        'approved_by',
        'approved_at',
        'catatan_persetujuan',
        'disbursed_at',
        'lunas_at',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_pengajuan' => 'decimal:2',
            'jumlah_disetujui' => 'decimal:2',
            'bunga_persen' => 'decimal:2',
            'approved_at' => 'datetime',
            'disbursed_at' => 'datetime',
            'lunas_at' => 'datetime',
        ];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    public function pengurus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}