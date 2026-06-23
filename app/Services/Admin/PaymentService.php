<?php

namespace App\Services\Admin;

use App\Models\Booking;

class PaymentService
{
    public function getAllPayments()
    {
        return Booking::with(['learner', 'tutor.user'])
            ->whereNotNull('payment_method')
            ->get();
    }

    public function approvePayment(int $id)
    {
        $booking = Booking::with(['learner', 'tutor.user', 'course'])->findOrFail($id);
        $booking->update([
            'status' => 'accepted', 
            'payment_status' => 'paid'
        ]);

        // Notifikasi untuk Learner
        $paidAmount = number_format($booking->grand_total, 0, ',', '.');
        \App\Models\Notification::create([
            'user_id' => $booking->learner_id,
            'role' => 'learner',
            'type' => 'payment',
            'title' => 'Pesanan Selesai, Cek Jadwal',
            'message' => "Pembayaran Anda sebesar Rp{$paidAmount} telah berhasil diverifikasi. Pesanan selesai, silakan cek menu Jadwal Belajar Anda.",
            'is_read' => false,
        ]);

        // Notifikasi untuk Tutor
        \App\Models\Notification::create([
            'user_id' => $booking->tutor->user_id,
            'role' => 'tutor',
            'type' => 'booking',
            'title' => 'Pesanan Baru Masuk!',
            'message' => "Hore! Ada Learner ({$booking->learner->name}) yang memesan sesi belajar dengan Anda. Silakan cek jadwal Anda.",
            'is_read' => false,
        ]);

        return $booking;
    }
}
