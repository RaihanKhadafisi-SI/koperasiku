<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * role dan is_active sengaja TIDAK dimasukkan di sini,
     * supaya tidak bisa diubah lewat input formulir biasa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function simpananDibuat(): HasMany
    {
        return $this->hasMany(Simpanan::class, 'dibuat_oleh');
    }

    public function simpananDiverifikasi(): HasMany
    {
        return $this->hasMany(Simpanan::class, 'diverifikasi_oleh');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}