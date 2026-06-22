<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\StatsService;

/**
 * @tags Admin Stats
 */
class StatsController extends Controller
{
    protected $statsService;

    public function __construct(StatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    public function index()
    {
        $stats = $this->statsService->getStats();

        return response()->json([
            'data' => $stats
        ]);
    }
}
