<?php

namespace App\Services\Admin;

use App\Models\Review;
use App\Models\ModerationLog;
use Illuminate\Support\Facades\Auth;

class ModerationService
{
    public function getReviewsToModerate()
    {
        return Review::with(['booking.learner', 'booking.tutor.user'])->whereIn('rating', [1, 2])->get();
    }

    public function deleteReview(int $id, ?string $reason = null)
    {
        $review = Review::with(['booking.learner', 'booking.tutor.user'])->findOrFail($id);
        
        $adminId = Auth::id(); // Fallback if admin_id is not passed
        
        ModerationLog::create([
            'admin_id' => $adminId,
            'action' => 'DIHAPUS',
            'reason' => $reason,
            'target_type' => 'Review',
            'target_id' => $review->id,
            'details' => [
                'rating' => $review->rating,
                'comment' => $review->comment,
                'learner_name' => $review->booking->learner->name ?? 'Unknown',
                'tutor_name' => $review->booking->tutor->user->name ?? 'Unknown',
            ]
        ]);

        $review->delete();
        return true;
    }

    public function processReview(int $id)
    {
        $review = Review::with(['booking.learner', 'booking.tutor.user'])->findOrFail($id);
        $review->update([
            'moderation_status' => 'DIPROSES'
        ]);

        ModerationLog::create([
            'admin_id' => Auth::id(),
            'action' => 'DIPROSES',
            'reason' => 'Admin menindak lanjuti komplain.',
            'target_type' => 'Review',
            'target_id' => $review->id,
            'details' => [
                'rating' => $review->rating,
                'comment' => $review->comment,
                'learner_name' => $review->booking->learner->name ?? 'Unknown',
                'tutor_name' => $review->booking->tutor->user->name ?? 'Unknown',
            ]
        ]);

        return $review;
    }

    public function resolveReview(int $id)
    {
        $review = Review::with(['booking.learner', 'booking.tutor.user'])->findOrFail($id);
        $review->update([
            'moderation_status' => 'SELESAI'
        ]);

        ModerationLog::create([
            'admin_id' => Auth::id(),
            'action' => 'SELESAI',
            'reason' => 'Komplain telah diselesaikan dengan baik.',
            'target_type' => 'Review',
            'target_id' => $review->id,
            'details' => [
                'rating' => $review->rating,
                'comment' => $review->comment,
                'learner_name' => $review->booking->learner->name ?? 'Unknown',
                'tutor_name' => $review->booking->tutor->user->name ?? 'Unknown',
            ]
        ]);

        return $review;
    }

    public function getRecentLogs()
    {
        return ModerationLog::with('admin')->orderBy('created_at', 'desc')->take(20)->get();
    }
}
