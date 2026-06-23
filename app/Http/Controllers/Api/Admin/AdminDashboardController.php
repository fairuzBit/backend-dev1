<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\AdminDashboardService;

class AdminDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get platform statistics for Admin Dashboard
     */
    public function stats()
    {
        $stats = $this->dashboardService->getStats();

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Get complaints (dummy for now)
     */
    public function complaints()
    {
        $complaints = $this->dashboardService->getComplaints();

        return response()->json([
            'data' => $complaints
        ]);
    }
}
