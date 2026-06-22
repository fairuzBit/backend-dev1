<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Tutor;
use App\Models\Booking;

class AdminDashboardService
{
    /**
     * Get platform statistics for Admin Dashboard
     */
    public function getStats()
    {
        $totalUsers = User::count();
        $totalTutors = Tutor::count();
        $totalTransactions = Booking::where('status', 'completed')->sum('total_price');
        $activeBookings = Booking::whereIn('status', ['pending', 'accepted', 'paid', 'ongoing'])->count();

        return [
            'total_users' => $totalUsers,
            'total_tutors' => $totalTutors,
            'total_transactions' => (float) $totalTransactions,
            'active_bookings' => $activeBookings
        ];
    }

    /**
     * Get complaints
     */
    public function getComplaints()
    {
        return [];
    }
}
