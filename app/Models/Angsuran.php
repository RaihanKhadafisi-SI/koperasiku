<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Angsuran extends Model
{
    protected $table = 'angsurans';

    protected $fillable = [
        'pinjaman_id',
        'angsuran_ke',
        'jatuh_tempo',
        'pokok',
        'bunga',
        'total_tagihan',
        'status',
        'tanggal_bayar',
        'dibayar_oleh',
    ];

    protected function casts(): array
    {
        return [
            'jatuh_tempo' => 'date',
            'tanggal_bayar' => 'date',
            'pokok' => 'decimal:2',
            'bunga' => 'decimal:2',
            'total_tagihan' => 'decimal:2',
        ];
    }

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id');
    }

    public function pembayar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibayar_oleh');
    }

    /**
     * Terlambat = belum dibayar dan tanggal jatuh tempo sudah lewat.
     */
    public function isTerlambat(): bool
    {
        return $this->status === 'belum_bayar'
            && $this->jatuh_tempo->lt(today());
    }
}