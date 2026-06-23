<?php

namespace Database\Seeders;

use App\Models\AvailabilitySlot;
use App\Models\MasterSlot;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;

class TutorAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $user1 = User::where('email', '111andiwijaya1@mhs.dinus.ac.id')->first();
        $user2 = User::where('email', '111sitinurhaliza2@mhs.dinus.ac.id')->first();
        
        if (!$user1 || !$user2) return;

        $tutor1 = Tutor::where('user_id', $user1->id)->first();
        $tutor2 = Tutor::where('user_id', $user2->id)->first();

        $slots = MasterSlot::take(3)->get();
        if ($slots->count() < 3) return;

        // Tutor 1 (Andi Wijaya) - Senin & Selasa
        AvailabilitySlot::firstOrCreate([
            'tutor_id' => $tutor1->id,
            'day_of_week' => 'Monday',
            'slot_id' => $slots[0]->id,
        ], ['is_active' => true]);

        AvailabilitySlot::firstOrCreate([
            'tutor_id' => $tutor1->id,
            'day_of_week' => 'Monday',
            'slot_id' => $slots[1]->id,
        ], ['is_active' => true]);

        AvailabilitySlot::firstOrCreate([
            'tutor_id' => $tutor1->id,
            'day_of_week' => 'Tuesday',
            'slot_id' => $slots[2]->id,
        ], ['is_active' => true]);

        // Tutor 2 (Siti Nurhaliza) - Rabu & Kamis
        AvailabilitySlot::firstOrCreate([
            'tutor_id' => $tutor2->id,
            'day_of_week' => 'Wednesday',
            'slot_id' => $slots[0]->id,
        ], ['is_active' => true]);

        AvailabilitySlot::firstOrCreate([
            'tutor_id' => $tutor2->id,
            'day_of_week' => 'Thursday',
            'slot_id' => $slots[1]->id,
        ], ['is_active' => true]);
    }
}
