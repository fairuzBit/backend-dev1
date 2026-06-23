<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Tutor;
use App\Models\TutorCourse;
use App\Models\User;
use Illuminate\Database\Seeder;

class TutorDiscoverySeeder extends Seeder
{
        public function run(): void
    {
        // 1. Buat Data Mata Kuliah
        $course1 = Course::firstOrCreate(['code' => 'CS101'], ['name' => 'Dasar Pemrograman']);
        $course2 = Course::firstOrCreate(['code' => 'BD101'], ['name' => 'Basis Data']);

        // 2. Ambil user tutor dari UserSeeder
        $tutorUser = User::where('email', '111andiwijaya1@mhs.dinus.ac.id')->first();
        
        if ($tutorUser) {
            $tutorProfile = Tutor::firstOrCreate(
                ['user_id' => $tutorUser->id],
                [
                    'bio' => 'Halo! Saya asisten lab pemrograman yang siap membantu.',
                    'rating_avg' => 4.8,
                    'total_reviews' => 12,
                    'price' => 50000, // HARGA DIPINDAH KESINI
                    'is_active' => true
                ]
            );

            // 3. Relasikan Tutor dengan Course (Tanpa Harga)
            TutorCourse::firstOrCreate([
                'tutor_id' => $tutorProfile->id,
                'course_id' => $course1->id,
            ]);

            TutorCourse::firstOrCreate([
                'tutor_id' => $tutorProfile->id,
                'course_id' => $course2->id,
            ]);
        }

        // 4. Tutor Kedua
        $tutorUser2 = User::where('email', '111sitinurhaliza2@mhs.dinus.ac.id')->first();

        $tutorProfile2 = Tutor::firstOrCreate(
            ['user_id' => $tutorUser2->id],
            [
                'bio' => 'Native speaker Bahasa Inggris, siap bantu kamu lulus TOEFL.',
                'rating_avg' => 5.0,
                'total_reviews' => 24,
                'price' => 75000, // HARGA DIPINDAH KESINI
                'is_active' => true
            ]
        );

        $course3 = Course::firstOrCreate(['code' => 'EN101'], ['name' => 'Bahasa Inggris Lanjut']);

        TutorCourse::firstOrCreate([
            'tutor_id' => $tutorProfile2->id,
            'course_id' => $course3->id,
        ]);
    }

}
