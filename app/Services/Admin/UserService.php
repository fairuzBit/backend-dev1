<?php

namespace App\Services\Admin;

use App\Models\User;

class UserService
{
    public function getAllUsers()
    {
        return User::with(['roles', 'tutor'])->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();
    }

    public function getUserById(int $id)
    {
        return User::with('roles')->findOrFail($id);
    }

    public function destroyUser(int $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return true;
    }

    public function suspendUser(int $id, string $duration)
    {
        $user = User::findOrFail($id);
        
        $days = 1;
        if ($duration === '1 Hari') $days = 1;
        elseif ($duration === '1 Minggu') $days = 7;
        elseif ($duration === '1 Bulan') $days = 30;
        elseif (is_numeric($duration)) $days = (int)$duration;

        $user->update([
            'suspended_until' => now()->addDays($days)
        ]);

        return $user;
    }

    public function unsuspendUser(int $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'suspended_until' => null
        ]);

        return $user;
    }
}
