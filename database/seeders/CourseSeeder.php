<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $coursesBySemester = [
            1 => [
                'Kalkulus', 'Fisika', 'Dasar Pemrograman', 'Interpersonal', 
                'Dasar Komputasi', 'Bahasa Indonesia', 'Agama Islam', 'Pengantar Teknologi Informasi'
            ],
            2 => [
                'Matriks Ruang Vektor', 'Pancasila', 'Algoritma Struktur Data', 'Matematika Diskrit'
            ],
            3 => [
                'Probabilitas dan Statistika', 'Logika Informatika', 'Basis Data', 
                'Sistem Operasi', 'Kriptografi', 'Pemrograman Web', 'Penambangan Data', 'Pengembangan Perangkat Lunak'
            ],
            4 => [
                'Otomata dan Teori Bahasa', 'Literasi Informasi', 'Pembelajaran Mesin', 
                'Jaringan Komputer', 'Sistem Basis Data', 'Rangkaian Logika Digital'
            ],
            5 => [
                'Keamanan Siber', 'Pemrograman Mobile', 'Rekayasa Perangkat Lunak Lanjut', 
                'Kecerdasan Buatan', 'Manajemen Proyek TI'
            ]
        ];

        $codeCounter = 1;

        foreach ($coursesBySemester as $semester => $courses) {
            foreach ($courses as $course) {
                Course::firstOrCreate(
                    ['code' => 'CS' . str_pad($codeCounter++, 3, '0', STR_PAD_LEFT)],
                    [
                        'name' => $course,
                        'semester' => $semester
                    ]
                );
            }
        }
    }
}
