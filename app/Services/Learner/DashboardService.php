<?php

namespace App\Services\Learner;

use App\Models\Booking;

class DashboardService
{
    /**
     * Mengambil jadwal kelas terdekat (Upcoming)
     */
    public function getUpcomingClass($learnerId)
    {
        return Booking::with(['tutor.user', 'course', 'bookingSlots.masterSlot'])
            ->where('learner_id', $learnerId)
            ->whereIn('status', ['accepted'])
            ->where('booking_date', '>=', now()->toDateString())
            ->orderBy('booking_date', 'asc')
            ->first();
    }

    /**
     * Mengambil statistik (Sesi, Jam Belajar, Matkul)
     */
    public function getStatistics($learnerId)
    {
        // Ambil semua transaksi yang sudah selesai beserta jumlah jam/slotnya
        $completedBookings = Booking::withCount('bookingSlots')
            ->where('learner_id', $learnerId)
            ->where('status', 'completed')
            ->get();

        $totalSessions = $completedBookings->count();
        
        // Kita asumsikan 1 slot = 2 jam belajar (tinggal diubah angkanya jika salah)
        $totalHours = $completedBookings->sum('booking_slots_count') * 2; 
        
        // Menghitung berapa matkul unik yang pernah dipesan
        $totalCourses = Booking::where('learner_id', $learnerId)
            ->where('status', 'completed')
            ->distinct('course_id')
            ->count('course_id');

        return [
            'total_sessions' => $totalSessions,
            'total_hours' => $totalHours,
            'total_courses' => $totalCourses,
        ];
    }

    /**
     * Mengambil 3 Tutor Rekomendasi (Berdasarkan Rating Tertinggi)
     */
    public function getRecommendedTutors()
    {
        return \App\Models\Tutor::with(['user', 'courses.course'])
            ->where('is_active', true)
            ->orderBy('rating_avg', 'desc')
            ->limit(3)
            ->get();
    }
}
