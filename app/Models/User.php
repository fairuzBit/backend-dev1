<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Laravel\Sanctum\HasApiTokens; // 1. Tambahkan ini untuk Sanctum
use Illuminate\Notifications\Notifiable; // 2. Tambahkan ini untuk Spatie
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    // 3. Masukkan Trait tersebut ke dalam class
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'nim',
        'phone',
        'suspended_until',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended_until' => 'immutable_datetime',
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();

    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relasi ke profil Tutor
     */
    public function tutor()
    {
        return $this->hasOne(Tutor::class);
    }
}
