<?php

namespace App\Services\Admin;

use App\Models\User;
use Exception;

class AdminUserService
{
    /**
     * Get all users
     */
    public function getAllUsers()
    {
        return User::with('roles')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Delete/Block user
     */
    public function destroyUser(int $id, int $currentUserId)
    {
        $user = User::findOrFail($id);
        
        // Mencegah admin menghapus dirinya sendiri
        if ($currentUserId == $user->id) {
            throw new Exception('Cannot delete yourself');
        }

        $user->delete();
        return true;
    }
}
