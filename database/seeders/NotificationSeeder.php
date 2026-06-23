<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $learner8 = User::where('email', '111dewilestari8@mhs.dinus.ac.id')->first();
        $learner9 = User::where('email', '111agusprayitno9@mhs.dinus.ac.id')->first();
        $learner10 = User::where('email', '111ninakarina10@mhs.dinus.ac.id')->first();
        $tutorUser1 = User::where('email', '111andiwijaya1@mhs.dinus.ac.id')->first();

        // 1. & 2. Learner 8
        if ($learner8) {
            Notification::firstOrCreate([
                'user_id' => $learner8->id,
                'title' => 'Menunggu Pembayaran',
            ], [
                'type' => 'payment',
                'message' => 'Anda memiliki tagihan yang belum dibayar untuk sesi dengan Tutor Andi Wijaya. Segera lakukan pembayaran.',
                'is_read' => false,
            ]);

            Notification::firstOrCreate([
                'user_id' => $learner8->id,
                'title' => 'Pembayaran Berhasil',
            ], [
                'type' => 'payment',
                'message' => 'Pembayaran Anda sebesar Rp51.000 telah berhasil diverifikasi oleh sistem. Sesi belajar Anda sudah aktif.',
                'is_read' => true,
            ]);
        }

        // 3. & 4. Learner 9
        if ($learner9) {
            Notification::firstOrCreate([
                'user_id' => $learner9->id,
                'title' => 'Pengingat: Sesi Belajar Besok',
            ], [
                'type' => 'session_reminder',
                'message' => 'Jangan lupa, Anda memiliki sesi belajar "Kalkulus" besok pukul 10:00 WIB.',
                'is_read' => false,
            ]);

            Notification::firstOrCreate([
                'user_id' => $learner9->id,
                'title' => 'Sesi Belajar Segera Dimulai!',
            ], [
                'type' => 'session_reminder',
                'message' => 'Siap-siap! Sesi belajar Anda dengan Tutor Siti Nurhaliza akan dimulai dalam 30 menit.',
                'is_read' => false,
            ]);
        }

        // 5, 6, 7. Learner 10
        if ($learner10) {
            Notification::firstOrCreate([
                'user_id' => $learner10->id,
                'title' => 'Pesanan Ditolak',
            ], [
                'type' => 'booking',
                'message' => 'Mohon maaf, pesanan Anda ditolak oleh Admin karena jadwal Tutor bentrok. Dana telah di-refund.',
                'is_read' => true,
            ]);

            Notification::firstOrCreate([
                'user_id' => $learner10->id,
                'title' => 'Pengajuan Tutor Diterima',
            ], [
                'type' => 'application',
                'message' => 'Dokumen pengajuan tutor Anda telah kami terima dan sedang dalam tahap peninjauan (pending) oleh Admin.',
                'is_read' => false,
            ]);

            Notification::firstOrCreate([
                'user_id' => $learner10->id,
                'title' => 'Pengajuan Tutor Ditolak',
            ], [
                'type' => 'application',
                'message' => 'Mohon maaf, pengajuan Anda sebagai Tutor ditolak. Catatan Admin: Transkrip nilai tidak terbaca. Harap upload ulang.',
                'is_read' => false,
            ]);
        }

        // 8, 9, 10, 11, 12. Tutor 1
        if ($tutorUser1) {
            Notification::firstOrCreate([
                'user_id' => $tutorUser1->id,
                'title' => 'Selamat! Anda Menjadi Tutor',
            ], [
                'type' => 'application',
                'message' => 'Selamat! Pengajuan Anda telah disetujui Admin. Sekarang Anda resmi menjadi Tutor di KonekDin.',
                'is_read' => true,
            ]);

            Notification::firstOrCreate([
                'user_id' => $tutorUser1->id,
                'title' => 'Pesanan Baru Masuk!',
            ], [
                'type' => 'booking',
                'message' => 'Hore! Ada Learner yang memesan sesi belajar dengan Anda. Silakan cek jadwal Anda.',
                'is_read' => false,
            ]);

            Notification::firstOrCreate([
                'user_id' => $tutorUser1->id,
                'title' => 'Ulasan Bintang 5 Baru!',
            ], [
                'type' => 'review',
                'message' => 'Anda mendapatkan ulasan bintang 5 dari Learner 8: "Tutornya sangat pintar!". Pertahankan!',
                'is_read' => false,
            ]);

            Notification::firstOrCreate([
                'user_id' => $tutorUser1->id,
                'title' => 'Pencairan Dana Berhasil',
            ], [
                'type' => 'payment',
                'message' => 'Dana dari sesi mengajar Anda telah berhasil ditransfer ke rekening tujuan.',
                'is_read' => true,
            ]);

            Notification::firstOrCreate([
                'user_id' => $tutorUser1->id,
                'title' => 'Pengingat Sesi Mengajar',
            ], [
                'type' => 'session_reminder',
                'message' => 'Sesi mengajar Anda dengan Learner 8 akan dimulai dalam 30 menit. Siapkan materi Anda!',
                'is_read' => false,
            ]);

            Notification::firstOrCreate([
                'user_id' => $tutorUser1->id,
                'title' => 'Pengingat H-1 Sesi Mengajar',
            ], [
                'type' => 'session_reminder',
                'message' => 'Halo! Jangan lupa, Anda memiliki jadwal mengajar besok dengan Learner 8. Pastikan persiapan materi sudah lengkap ya!',
                'is_read' => false,
            ]);
        }
    }
}
