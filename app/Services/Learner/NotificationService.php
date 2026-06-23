<?php

namespace App\Services\Learner;

use App\Models\Notification;

class NotificationService
{
    public function getUserNotifications(int $userId)
    {
        return Notification::where('user_id', $userId)
            ->where('role', 'learner')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function markAsRead(int $userId, int $notificationId)
    {
        $notification = Notification::where('user_id', $userId)
            ->where('id', $notificationId)
            ->firstOrFail();
            
        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);
        
        return $notification;
    }

    public function markAllAsRead(int $userId)
    {
        Notification::where('user_id', $userId)
            ->where('role', 'learner')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }
}
