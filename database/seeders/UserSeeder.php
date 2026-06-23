<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');
        $now = now();

        // 1. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@mhs.dinus.ac.id'],
            [
                'name' => 'Admin Baik',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'admin',
            ]
        );
        $admin->assignRole('admin');

        // ==========================================
        // TUTORS (5 Users)
        // ==========================================
        
        $tutor1 = User::firstOrCreate(
            ['email' => '111andiwijaya1@mhs.dinus.ac.id'],
            [
                'name' => 'Andi Wijaya',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'tutor',
                'nim' => 'A11.2023.10001',
                'phone' => '081234567801',
            ]
        );
        $tutor1->assignRole(['learner', 'tutor']);

        $tutor2 = User::firstOrCreate(
            ['email' => '111sitinurhaliza2@mhs.dinus.ac.id'],
            [
                'name' => 'Siti Nurhaliza',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'tutor',
                'nim' => 'A11.2023.10002',
                'phone' => '081234567802',
            ]
        );
        $tutor2->assignRole(['learner', 'tutor']);

        $tutor3 = User::firstOrCreate(
            ['email' => '111rezarahadian3@mhs.dinus.ac.id'],
            [
                'name' => 'Reza Rahadian',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'tutor',
                'nim' => 'A11.2023.10003',
                'phone' => '081234567803',
            ]
        );
        $tutor3->assignRole(['learner', 'tutor']);

        $tutor4 = User::firstOrCreate(
            ['email' => '111putriamanda4@mhs.dinus.ac.id'],
            [
                'name' => 'Putri Amanda',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'tutor',
                'nim' => 'A11.2023.10004',
                'phone' => '081234567804',
            ]
        );
        $tutor4->assignRole(['learner', 'tutor']);

        $tutor5 = User::firstOrCreate(
            ['email' => '111gilangdirga5@mhs.dinus.ac.id'],
            [
                'name' => 'Gilang Dirga',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'tutor',
                'nim' => 'A11.2023.10005',
                'phone' => '081234567805',
            ]
        );
        $tutor5->assignRole(['learner', 'tutor']);


        // ==========================================
        // LEARNERS (5 Users)
        // ==========================================

        $learner1 = User::firstOrCreate(
            ['email' => '111budisantoso6@mhs.dinus.ac.id'],
            [
                'name' => 'Budi Santoso',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'learner',
                'nim' => 'A11.2023.10006',
                'phone' => '081234567806',
            ]
        );
        $learner1->assignRole('learner');

        $learner2 = User::firstOrCreate(
            ['email' => '111rinamelati7@mhs.dinus.ac.id'],
            [
                'name' => 'Rina Melati',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'learner',
                'nim' => 'A11.2023.10007',
                'phone' => '081234567807',
            ]
        );
        $learner2->assignRole('learner');

        $learner3 = User::firstOrCreate(
            ['email' => '111dewilestari8@mhs.dinus.ac.id'],
            [
                'name' => 'Dewi Lestari',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'learner',
                'nim' => 'A11.2023.10008',
                'phone' => '081234567808',
            ]
        );
        $learner3->assignRole('learner');

        $learner4 = User::firstOrCreate(
            ['email' => '111agusprayitno9@mhs.dinus.ac.id'],
            [
                'name' => 'Agus Prayitno',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'learner',
                'nim' => 'A11.2023.10009',
                'phone' => '081234567809',
            ]
        );
        $learner4->assignRole('learner');

        $learner5 = User::firstOrCreate(
            ['email' => '111ninakarina10@mhs.dinus.ac.id'],
            [
                'name' => 'Nina Karina',
                'password' => $password,
                'email_verified_at' => $now,
                'role' => 'learner',
                'nim' => 'A11.2023.10010',
                'phone' => '081234567810',
            ]
        );
        $learner5->assignRole('learner');
    }
}
