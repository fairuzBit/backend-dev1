<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tutor\ReviewService;

/**
 * @tags Tutor Review
 */
class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index(Request $request)
    {
        $tutorId = $request->user()->tutor->id ?? null;

        if (!$tutorId) {
            return response()->json(['message' => 'Anda bukan tutor'], 403);
        }

        $rating = $request->query('rating');
        $perPage = $request->query('per_page', 10);

        $reviews = $this->reviewService->getReviews($tutorId, $rating, $perPage);
        $summary = $this->reviewService->getReviewsSummary($tutorId);

        $reviews->getCollection()->transform(function ($review) {
            $sessionTime = '-';
            $sessionDate = $review->booking->booking_date ?? '-';
            if ($review->booking && $review->booking->bookingSlots->isNotEmpty()) {
                $masterSlots = $review->booking->bookingSlots->map(function($s) {
                    return $s->masterSlot;
                })->filter()->sortBy('start_time')->values();
                if ($masterSlots->count() > 0) {
                    $first = $masterSlots->first();
                    $last = $masterSlots->last();
                    
                    $start = $first->start_time ? substr($first->start_time, 0, 5) : '';
                    $end = $last->end_time ? substr($last->end_time, 0, 5) : '';
                    $sessionTime = $start && $end ? "$start - $end" : '-';
                }
            }

            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'learner' => [
                    'id' => $review->booking->learner->id ?? null,
                    'name' => $review->booking->learner->name ?? 'Unknown',
                    'avatar' => $review->booking->learner->avatar ?? null,
                ],
                'course' => [
                    'id' => $review->booking->course->id ?? null,
                    'name' => $review->booking->course->name ?? 'Unknown',
                ],
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                'session_date' => $sessionDate,
                'session_time' => $sessionTime
            ];
        });

        return response()->json([
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
            'summary' => $summary
        ]);
    }
}
