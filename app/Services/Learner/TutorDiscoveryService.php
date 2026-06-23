<?php

namespace App\Services\Learner;

use App\Models\Tutor;

class TutorDiscoveryService
{
    /**
     * Get active tutors with search, filters, and pagination.
     */
    public function getAllTutors(array $filters = [], int $perPage = 9)
    {
        $query = Tutor::with(['user', 'courses.course', 'availabilitySlots.masterSlot'])
            ->withCount(['bookings as total_sessions' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->where('is_active', true)
            ->whereHas('availabilitySlots', function ($q) {
                $q->where('is_active', true);
            });

        // Filter berdasarkan pencarian (nama tutor)
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter berdasarkan Mata Kuliah
        if (!empty($filters['course_id'])) {
            $courseId = $filters['course_id'];
            $query->whereHas('courses', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get specific tutor details.
     */
    public function getTutorById(int $id)
    {
        return Tutor::with(['user', 'courses.course', 'availabilitySlots.masterSlot', 'reviews.booking.learner'])
            ->withCount(['bookings as total_sessions' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->findOrFail($id);
    }
}
