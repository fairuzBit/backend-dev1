<?php

namespace App\Services\Tutor;

use App\Models\Notification;

class NotificationService
{
    public function getNotifications(int $userId)
    {
        return Notification::where('user_id', $userId)
            ->where('role', 'tutor')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function markAllAsRead(int $userId)
    {
        Notification::where('user_id', $userId)
            ->where('role', 'tutor')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }
}
