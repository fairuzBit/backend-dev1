<?php

namespace App\Services\Tutor;

use App\Models\AvailabilitySlot;
use Exception;

class AvailabilityService
{
    /**
     * Get tutor availabilities
     */
    public function getAvailabilities(int $tutorId)
    {
        return AvailabilitySlot::with('masterSlot')
            ->where('tutor_id', $tutorId)
            ->get();
    }

    /**
     * Store tutor availabilities
     */
    public function storeAvailabilities(int $tutorId, array $slots)
    {
        // Hapus ketersediaan lama, ganti yang baru
        AvailabilitySlot::where('tutor_id', $tutorId)->delete();

        $newSlots = [];
        foreach ($slots as $slot) {
            $newSlots[] = AvailabilitySlot::create([
                'tutor_id' => $tutorId,
                'day_of_week' => $slot['day_of_week'],
                'slot_id' => $slot['master_slot_id'],
                'is_active' => $slot['is_active'] ?? true,
            ]);
        }

        return collect($newSlots);
    }
}
