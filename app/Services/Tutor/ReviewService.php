<?php

namespace App\Services\Tutor;

use App\Models\Review;

class ReviewService
{
    public function getReviews(int $tutorId, $rating = null, $perPage = 10)
    {
        $query = Review::with(['booking.learner', 'booking.course', 'booking.bookingSlots.masterSlot'])
            ->whereHas('booking', function($q) use ($tutorId) {
                $q->where('tutor_id', $tutorId);
            });

        if ($rating) {
            $query->where('rating', $rating);
        }

        return $query->paginate($perPage);
    }

    public function getReviewsSummary(int $tutorId)
    {
        $reviewsQuery = Review::whereHas('booking', function($query) use ($tutorId) {
            $query->where('tutor_id', $tutorId);
        });

        $totalReviews = $reviewsQuery->count();
        $averageRating = $totalReviews > 0 ? (float) $reviewsQuery->avg('rating') : 0;
        $satisfactionPercent = $totalReviews > 0 ? round(($averageRating / 5) * 100) : 0;

        $distributionData = (clone $reviewsQuery)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $distribution = [
            [ 'rating' => 5, 'count' => $distributionData[5] ?? 0 ],
            [ 'rating' => 4, 'count' => $distributionData[4] ?? 0 ],
            [ 'rating' => 3, 'count' => $distributionData[3] ?? 0 ],
            [ 'rating' => 2, 'count' => $distributionData[2] ?? 0 ],
            [ 'rating' => 1, 'count' => $distributionData[1] ?? 0 ],
        ];

        return [
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
            'satisfaction_percent' => $satisfactionPercent,
            'rating_distribution' => $distribution
        ];
    }
}
