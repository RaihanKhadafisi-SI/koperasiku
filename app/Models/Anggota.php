<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    protected $table = 'anggota';

    protected $fillable = [
        'no_anggota',
        'nik',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'email',
        'tanggal_bergabung',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_bergabung' => 'date',
        ];
    }

    public function simpanan(): HasMany
    {
        return $this->hasMany(Simpanan::class, 'anggota_id');
    }

    public function pinjaman(): HasMany
    {
        return $this->hasMany(Pinjaman::class, 'anggota_id');
    }
}