<?php

namespace App\Http\Controllers\Api\Learner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Learner\DashboardService;
use App\Http\Resources\BookingResource;

/**
 * @tags Learner - Dashboard
 */
class DashboardController extends Controller
{
    protected $dashboardService;

    // Masukkan/Inject Service ke dalam Controller
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }


    public function index(Request $request)
    {
        $upcomingClass = $this->dashboardService->getUpcomingClass($request->user()->id);
        $recommendedTutors = $this->dashboardService->getRecommendedTutors();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil',
            'data' => [
                'welcome_message' => 'Halo, ' . $request->user()->name,
                // Bungkus variabel upcomingClass ke dalam Resource
                'next_class' => $upcomingClass ? new BookingResource($upcomingClass) : null,
                // Gunakan TutorResource dari sebelumnya
                'recommended_tutors' => \App\Http\Resources\TutorResource::collection($recommendedTutors)
            ]
        ]);
    }


    public function stats(Request $request)
    {
        $stats = $this->dashboardService->getStatistics($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Data statistik berhasil diambil',
            'data' => $stats
        ]);
    }
}
