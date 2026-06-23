<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TutorApplication;
use App\Models\Course;

class TutorApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('admin')->first();
        $course = Course::first();
        
        // Learner 6 dan 7 kita asumsikan sedang apply jadi tutor
        $learner6 = User::where('email', '111budisantoso6@mhs.dinus.ac.id')->first();
        $learner7 = User::where('email', '111rinamelati7@mhs.dinus.ac.id')->first();
        
        if ($learner6) {
            TutorApplication::firstOrCreate(
                ['user_id' => $learner6->id],
                [
                    'course_id' => $course->id,
                    'grade' => 'A',
                    'transcript_files' => ["transcripts/dummy_learner6.pdf"],
                    'status' => 'pending',
                ]
            );
        }
        
        if ($learner7) {
            TutorApplication::firstOrCreate(
                ['user_id' => $learner7->id],
                [
                    'course_id' => $course->id,
                    'grade' => 'A',
                    'transcript_files' => ["transcripts/dummy_learner7.pdf"],
                    'status' => 'rejected',
                    'admin_note' => 'Transkrip tidak terbaca, mohon scan ulang.',
                    'approved_by' => $admin->id ?? 1,
                ]
            );
        }
    }
}
