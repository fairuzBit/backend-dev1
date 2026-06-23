<?php

namespace App\Services\Tutor;

use App\Models\Booking;

class DashboardService
{
    public function getStats(int $tutorId, float $rating)
    {
        $totalSessions = Booking::where('tutor_id', $tutorId)->where('status', 'completed')->count();
        $totalEarnings = Booking::where('tutor_id', $tutorId)->where('status', 'completed')->sum('total_price');

        return [
            'total_earnings' => (int) $totalEarnings,
            'total_sessions' => $totalSessions,
            'rating' => (float) $rating,
        ];
    }
}
