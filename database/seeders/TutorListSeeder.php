<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Tutor;
use App\Models\TutorCourse;
use App\Models\User;
use Illuminate\Database\Seeder;

class TutorListSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil User yang sudah dibuat di UserSeeder
        $user1 = User::where('email', '111andiwijaya1@mhs.dinus.ac.id')->first();
        $user2 = User::where('email', '111sitinurhaliza2@mhs.dinus.ac.id')->first();
        $user3 = User::where('email', '111rezarahadian3@mhs.dinus.ac.id')->first();
        $user4 = User::where('email', '111putriamanda4@mhs.dinus.ac.id')->first();
        $user5 = User::where('email', '111gilangdirga5@mhs.dinus.ac.id')->first();
        
        // Cek jika user tidak ada, jangan dilanjutkan agar tidak error
        if (!$user1 || !$user2 || !$user4 || !$user5) return;

        // Ambil ID dari Course untuk relasi
        $courseKalkulus = Course::where('name', 'Kalkulus')->first();
        $courseFisika = Course::where('name', 'Fisika')->first();
        $courseDaspro = Course::where('name', 'Dasar Pemrograman')->first();
        $courseWeb = Course::where('name', 'Pemrograman Web')->first();
        $courseSBD = Course::where('name', 'Sistem Basis Data')->first();

        // 1. Andi Wijaya
        $tutor1 = Tutor::firstOrCreate(
            ['user_id' => $user1->id],
            [
                'bio' => 'Berpengalaman dalam mengajar pemrograman dasar.',
                'rating_avg' => 4.5,
                'total_reviews' => 10,
                'price' => 50000,
                'is_active' => true,
                'ipk' => 3.5,
            ]
        );
        if ($courseKalkulus) TutorCourse::firstOrCreate(['tutor_id' => $tutor1->id, 'course_id' => $courseKalkulus->id]);
        if ($courseDaspro) TutorCourse::firstOrCreate(['tutor_id' => $tutor1->id, 'course_id' => $courseDaspro->id]);

        // 2. Siti Nurhaliza
        $tutor2 = Tutor::firstOrCreate(
            ['user_id' => $user2->id],
            [
                'bio' => 'Asisten praktikum handal, siap bantu memahami konsep secara santai.',
                'rating_avg' => 4.6,
                'total_reviews' => 15,
                'price' => 55000,
                'is_active' => true,
                'ipk' => 3.6,
            ]
        );
        if ($courseFisika) TutorCourse::firstOrCreate(['tutor_id' => $tutor2->id, 'course_id' => $courseFisika->id]);

        // 3. Reza Rahadian
        if ($user3 = User::where('email', '111rezarahadian3@mhs.dinus.ac.id')->first()) {
            $tutor3 = Tutor::firstOrCreate(
                ['user_id' => $user3->id],
                [
                    'bio' => 'Expert dalam analisis sistem dan basis data.',
                    'rating_avg' => 4.7,
                    'total_reviews' => 20,
                    'price' => 60000,
                    'is_active' => true,
                    'ipk' => 3.7,
                ]
            );
            if ($courseSBD) TutorCourse::firstOrCreate(['tutor_id' => $tutor3->id, 'course_id' => $courseSBD->id]);
        }

        // 4. Putri Amanda
        $tutor4 = Tutor::firstOrCreate(
            ['user_id' => $user4->id],
            [
                'bio' => 'Suka hitungan? Yuk taklukkan soal-soal sulit bersamaku!',
                'rating_avg' => 4.8,
                'total_reviews' => 25,
                'price' => 65000,
                'is_active' => true,
                'ipk' => 3.8,
            ]
        );
        if ($courseKalkulus) TutorCourse::firstOrCreate(['tutor_id' => $tutor4->id, 'course_id' => $courseKalkulus->id]);
        if ($courseWeb) TutorCourse::firstOrCreate(['tutor_id' => $tutor4->id, 'course_id' => $courseWeb->id]);

        // 5. Gilang Dirga
        $tutor5 = Tutor::firstOrCreate(
            ['user_id' => $user5->id],
            [
                'bio' => 'Freelance designer, siap bagi ilmu desain visual untuk pemula.',
                'rating_avg' => 4.9,
                'total_reviews' => 30,
                'price' => 70000,
                'is_active' => true,
                'ipk' => 3.9,
            ]
        );
        if ($courseDaspro) TutorCourse::firstOrCreate(['tutor_id' => $tutor5->id, 'course_id' => $courseDaspro->id]);
    }
}
