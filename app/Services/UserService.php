<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Mendaftarkan user baru dan memberikan role Spatie
     */
    public function registerUser(array $data)
    {
        // 1. Buat User ke database
        $user = User::create([
            'name' => $data['fullName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'nim' => $data['nim'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        // 2. Pasang role menggunakan Spatie
        $user->assignRole('learner');

        return $user;
    }
}
