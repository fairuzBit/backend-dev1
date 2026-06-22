<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
    {
        $slots = [
            ['code' => 'Sesi-1', 'start_time' => '07:00:00', 'end_time' => '07:50:00'],
            ['code' => 'Sesi-2', 'start_time' => '07:50:00', 'end_time' => '08:40:00'],
            ['code' => 'Sesi-3', 'start_time' => '08:40:00', 'end_time' => '09:30:00'],
            ['code' => 'Sesi-4', 'start_time' => '09:30:00', 'end_time' => '10:20:00'],
            ['code' => 'Sesi-5', 'start_time' => '10:20:00', 'end_time' => '11:10:00'],
            ['code' => 'Sesi-6', 'start_time' => '11:10:00', 'end_time' => '12:00:00'],
            ['code' => 'Sesi-7', 'start_time' => '12:30:00', 'end_time' => '13:20:00'],
            ['code' => 'Sesi-8', 'start_time' => '13:20:00', 'end_time' => '14:10:00'],
            ['code' => 'Sesi-9', 'start_time' => '14:10:00', 'end_time' => '15:00:00'],
            ['code' => 'Sesi-10', 'start_time' => '15:30:00', 'end_time' => '16:20:00'],
            ['code' => 'Sesi-11', 'start_time' => '16:20:00', 'end_time' => '17:10:00'],
            ['code' => 'Sesi-12', 'start_time' => '17:10:00', 'end_time' => '18:00:00'],
        ];

        foreach ($slots as $slot) {
            \App\Models\MasterSlot::updateOrCreate(
                ['code' => $slot['code']],
                ['start_time' => $slot['start_time'], 'end_time' => $slot['end_time']]
            );
        }
    }

}
