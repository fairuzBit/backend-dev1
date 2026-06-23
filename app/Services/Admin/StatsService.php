<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Booking;
use App\Models\Review;
use App\Models\TutorApplication;

use App\Models\Tutor;
use App\Models\Course;

class StatsService
{
    public function getStats()
    {
        $activeComplaints = Review::whereIn('rating', [1, 2])
            ->where('moderation_status', 'MENUNGGU TINJAUAN')
            ->count();

        $topTutors = Tutor::withCount(['bookings as completed_sessions_count' => function ($query) {
            $query->where('status', 'completed');
        }])->with('user')
            ->orderByDesc('completed_sessions_count')
            ->take(3)
            ->get()
            ->map(function ($tutor) {
                return [
                    'id' => $tutor->id,
                    'name' => $tutor->user->name ?? 'Unknown',
                    'rating' => round($tutor->rating_avg, 1),
                    'sessions' => $tutor->completed_sessions_count,
                    'avatar' => ($tutor->user && $tutor->user->avatar) ? (str_starts_with($tutor->user->avatar, 'data:image') || str_starts_with($tutor->user->avatar, 'http') ? $tutor->user->avatar : asset('storage/' . $tutor->user->avatar)) : null,
                ];
            });

        $popularCourses = Course::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->take(3)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'bookings' => $course->bookings_count
                ];
            });

        $pendingPayments = Booking::whereNotNull('payment_method')
            ->where('status', 'pending')
            ->count();

        return [
            'total_learners' => User::role('learner')->count(),
            'total_tutors' => User::role('tutor')->count(),
            'active_complaints' => $activeComplaints,
            'pending_verifications' => TutorApplication::where('status', 'pending')->count(),
            'pending_payments' => $pendingPayments,
            'new_complaints' => $activeComplaints, // Alias
            'aktivitas_terbaru' => $this->getRecentActivities(),
            'top_tutors' => $topTutors,
            'mata_kuliah_populer' => $popularCourses,
            
            // Legacy fallbacks just in case
            'total_users' => User::count(),
            'total_transactions' => Booking::where('status', 'completed')->sum('total_price'),
        ];
    }

    private function getRecentActivities()
    {
        $activities = collect();

        // 1. Tutor Applications
        $applications = TutorApplication::with('user')->latest()->take(5)->get();
        foreach ($applications as $app) {
            $activities->push([
                'id' => 'app_'.$app->id,
                'user_name' => $app->user->name ?? 'Unknown',
                'activity' => 'Pengajuan Tutor Baru',
                'time' => $app->created_at,
                'status' => $app->status === 'pending' ? 'Menunggu' : ucfirst($app->status),
                'type' => 'application'
            ]);
        }

        // 2. Bookings
        $bookings = Booking::with('learner')->latest()->take(5)->get();
        foreach ($bookings as $booking) {
            $activities->push([
                'id' => 'booking_'.$booking->id,
                'user_name' => $booking->learner->name ?? 'Unknown',
                'activity' => 'Pesanan Sesi Baru',
                'time' => $booking->created_at,
                'status' => ucfirst($booking->status),
                'type' => 'booking'
            ]);
        }

        // 3. Reviews (Complaints)
        $reviews = Review::with('booking.learner')->whereIn('rating', [1, 2])->latest()->take(5)->get();
        foreach ($reviews as $review) {
            $activities->push([
                'id' => 'review_'.$review->id,
                'user_name' => $review->booking->learner->name ?? 'Unknown',
                'activity' => 'Komplain Sesi',
                'time' => $review->created_at,
                'status' => $review->moderation_status ?? 'Diterima',
                'type' => 'review'
            ]);
        }

        return $activities->sortByDesc('time')->values()->take(5)->map(function ($item) {
            $item['time_formatted'] = $item['time']->diffForHumans();
            return $item;
        })->toArray();
    }
}
