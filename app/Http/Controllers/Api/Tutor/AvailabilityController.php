<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AvailabilitySlot;
use App\Services\Tutor\AvailabilityService;
use App\Http\Requests\Tutor\StoreAvailabilityRequest;
use Illuminate\Support\Facades\Log;

/**
 * @tags Tutor Availability
 */
class AvailabilityController extends Controller
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }
    /**
     * Get Tutor Availability
     */
    public function index(Request $request)
    {
        $tutorId = $request->user()->tutor->id ?? null;

        if (!$tutorId) {
            return response()->json(['message' => 'Anda bukan tutor'], 403);
        }

        $availabilities = $this->availabilityService->getAvailabilities($tutorId);

        return response()->json([
            'data' => $availabilities->map(function ($slot) {
                $dayMap = [
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                    'Sunday' => 'Minggu',
                ];
                
                $time = null;
                if ($slot->masterSlot) {
                    $start = date('H:i', strtotime($slot->masterSlot->start_time));
                    $end = date('H:i', strtotime($slot->masterSlot->end_time));
                    $time = $start . ' - ' . $end;
                }

                return [
                    'id' => $slot->id,
                    'day' => $dayMap[$slot->day_of_week] ?? $slot->day_of_week,
                    'time' => $time,
                    'status' => $slot->is_active ? 'AVAILABLE' : 'NON AVAILABLE',
                ];
            })
        ]);
    }

    /**
     * Set Tutor Availability
     */
    public function store(StoreAvailabilityRequest $request)
    {
        $tutorId = $request->user()->tutor->id ?? null;

        if (!$tutorId) {
            return response()->json(['message' => 'Anda bukan tutor'], 403);
        }

        if (empty($request->user()->phone)) {
            return response()->json(['message' => 'Silakan lengkapi nomor telepon di profil Anda sebelum mengatur jadwal ketersediaan.'], 422);
        }

        try {
            $newSlots = $this->availabilityService->storeAvailabilities($tutorId, $request->slots);

            return response()->json([
                'message' => 'Jadwal berhasil diperbarui',
                'data' => $newSlots
            ]);
        } catch (\Exception $e) {
            Log::error('Availability save error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'payload' => $request->all()]);
            return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}
