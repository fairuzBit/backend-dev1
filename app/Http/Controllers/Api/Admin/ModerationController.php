<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\ModerationService;

/**
 * @tags Admin Moderation
 */
class ModerationController extends Controller
{
    protected $moderationService;

    public function __construct(ModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    public function reviews()
    {
        $reviews = $this->moderationService->getReviewsToModerate();

        return response()->json([
            'success' => true,
            'message' => 'Daftar ulasan yang butuh moderasi berhasil diambil',
            'data' => [
                'stats' => [
                    'total_negative' => $reviews->count(),
                    'pending' => $reviews->filter(function($r) { return empty($r->moderation_status) || $r->moderation_status === 'MENUNGGU TINJAUAN'; })->count(),
                    'processing' => $reviews->where('moderation_status', 'DIPROSES')->count(),
                    'resolved' => $reviews->where('moderation_status', 'SELESAI')->count()
                ],
                'reviews' => $reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'tanggal' => $review->created_at->format('d M Y, H:i'),
                        'learner_id' => $review->booking->learner->id ?? null,
                        'learner_name' => $review->booking->learner->name ?? 'Unknown',
                        'tutor_id' => $review->booking->tutor->id ?? null,
                        'tutor_name' => $review->booking->tutor->user->name ?? 'Unknown',
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'moderation_status' => empty($review->moderation_status) ? 'MENUNGGU TINJAUAN' : $review->moderation_status
                    ];
                })->values()
            ]
        ]);
    }

    public function destroyReview(Request $request, $id)
    {
        $reason = $request->input('reason', 'Penghapusan oleh Admin');
        $this->moderationService->deleteReview($id, $reason);

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil dihapus secara permanen'
        ]);
    }

    public function logs()
    {
        $logs = $this->moderationService->getRecentLogs();
        
        return response()->json([
            'success' => true,
            'data' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'tanggal' => $log->created_at->format('d M Y, H:i'),
                    'admin_name' => $log->admin->name ?? 'Admin',
                    'action' => $log->action,
                    'reason' => $log->reason,
                    'details' => $log->details
                ];
            })
        ]);
    }

    public function processReview($id)
    {
        $review = $this->moderationService->processReview($id);

        return response()->json([
            'success' => true,
            'message' => 'Status ulasan diperbarui menjadi DIPROSES',
            'data' => $review
        ]);
    }

    public function resolveReview($id)
    {
        $review = $this->moderationService->resolveReview($id);

        return response()->json([
            'success' => true,
            'message' => 'Status ulasan diperbarui menjadi SELESAI',
            'data' => $review
        ]);
    }
}
