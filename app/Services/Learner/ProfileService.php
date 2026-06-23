<?php

namespace App\Services\Learner;

use App\Models\User;

class ProfileService
{
    /**
     * Mengambil profil user berdasarkan ID
     */
    public function getProfile(int $userId)
    {
        return User::findOrFail($userId);
    }

    /**
     * Memperbarui profil user
     */
    public function updateProfile(int $userId, array $data)
    {
        $user = User::findOrFail($userId);
        $user->update($data);
        return $user;
    }

    /**
     * Cek status aplikasi tutor
     */
    public function tutorApplicationStatus(int $userId)
    {
        $application = \App\Models\TutorApplication::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$application) {
            return 'none';
        }

        return $application->status; // pending, approved, rejected
    }
}
