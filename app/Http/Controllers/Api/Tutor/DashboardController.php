<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tutor\DashboardService;

/**
 * @tags Tutor Dashboard
 */
class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get Tutor Dashboard Stats
     */
    public function index(Request $request)
    {
        $tutorId = $request->user()->tutor->id ?? null;

        if (!$tutorId) {
            return response()->json(['message' => 'Anda bukan tutor'], 403);
        }

        $rating = $request->user()->tutor->rating_avg ?? 0;

        $stats = $this->dashboardService->getStats($tutorId, $rating);

        return response()->json([
            'data' => $stats
        ]);
    }
}
