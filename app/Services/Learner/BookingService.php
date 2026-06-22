<?php

namespace App\Services\Learner;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\MasterSlot;
use App\Models\Tutor;
use Illuminate\Support\Facades\DB;
use Exception;

class BookingService
{
    /**
     * Memesan sesi tutor (Bisa pilih banyak slot sekaligus)
     */
    public function createBooking($learnerId, $data)
    {
        $tutor = Tutor::findOrFail($data['tutor_id']);
        
        if (!$tutor->is_active) {
            throw new Exception("Gagal membuat pesanan. Tutor yang bersangkutan sedang tidak aktif menerima pemesanan saat ini.");
        }

        $slotIds = $data['slot_ids']; // Bentuknya array, contoh: [1, 2]
        
        // Harga total = Harga Tutor x Jumlah Slot yang dipilih
        $totalPrice = $tutor->price * count($slotIds);
        $serviceFee = 1000;
        $grandTotal = $totalPrice + $serviceFee;

        // Pakai DB Transaction agar jika gagal di tengah jalan, data di-rollback
        DB::beginTransaction();
        try {
            // 1. Buat Struk Transaksi (Tabel bookings)
            $booking = Booking::create([
                'tutor_id' => $tutor->id,
                'course_id' => $data['course_id'],
                'learner_id' => $learnerId,
                'booking_date' => $data['booking_date'],
                'total_price' => $totalPrice,
                'service_fee' => $serviceFee,
                'grand_total' => $grandTotal,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_expired_at' => now()->addMinutes(15)
            ]);

            // 2. Masukkan Rincian Slot dan Cek Jadwal Bentrok
            $dayOfWeek = date('l', strtotime($data['booking_date']));

            foreach ($slotIds as $slotId) {
                // Cek apakah tutor membuka slot ini di hari tersebut
                $isAvailable = \App\Models\AvailabilitySlot::where('tutor_id', $tutor->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('slot_id', $slotId)
                    ->where('is_active', true)
                    ->exists();
                    
                if (!$isAvailable) {
                    throw new Exception("Gagal. Tutor tidak tersedia pada waktu yang Anda pilih.");
                }

                // Cek apakah slot ini di hari itu sudah dipesan orang lain (dan belum di-cancel)
                $isBooked = BookingSlot::whereHas('booking', function ($q) use ($tutor, $data) {
                    $q->where('tutor_id', $tutor->id)
                      ->where('booking_date', $data['booking_date'])
                      ->whereIn('status', ['pending', 'accepted']);
                })->where('slot_id', $slotId)->exists();

                if ($isBooked) {
                    throw new Exception("Gagal. Salah satu slot yang Anda pilih baru saja dipesan orang lain.");
                }

                // Ambil data jam dari MasterSlot
                $masterSlot = MasterSlot::findOrFail($slotId);

                // Masukkan ke keranjang BookingSlot
                BookingSlot::create([
                    'booking_id' => $booking->id,
                    'slot_id' => $slotId,
                    'start_time' => $masterSlot->start_time,
                    'end_time' => $masterSlot->end_time,
                ]);
            }

            DB::commit();
            return $booking->load('bookingSlots');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e; // Lempar error ke Controller
        }
    }

    /**
     * Mengambil Semua Pesanan Aktif (Untuk Menu: Detail Pesanan)
     * Menampilkan yang belum dibayar & yang sudah dibayar tapi belum selesai
     */
    public function getActiveOrders($learnerId)
    {
        return Booking::with(['tutor.user', 'course', 'bookingSlots.masterSlot'])
            ->where('learner_id', $learnerId)
            ->whereIn('status', ['pending', 'accepted'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mengambil Jadwal Mendatang (Untuk Menu: Jadwal Belajar)
     * Hanya menampilkan yang sudah LUNAS dan DI-ACC Tutor
     */
    public function getUpcomingSchedules($learnerId)
    {
        return Booking::with(['tutor.user', 'course', 'bookingSlots.masterSlot'])
            ->where('learner_id', $learnerId)
            ->where('status', 'accepted')
            ->where('payment_status', 'paid')
            ->where('booking_date', '>=', now()->toDateString())
            ->orderBy('booking_date', 'asc')
            ->get();
    }

    /**
     * Mengambil Riwayat Masa Lalu (History)
     */
    public function getHistory($learnerId)
    {
        return Booking::with(['tutor.user', 'course', 'bookingSlots.masterSlot', 'review'])
            ->where('learner_id', $learnerId)
            ->whereIn('status', ['completed', 'cancelled', 'rejected'])
            ->orderBy('booking_date', 'desc')
            ->get();
    }

    /**
     * Mengambil Detail Pesanan Spesifik (Untuk halaman Detail Pesanan)
     */
    public function getBookingDetail($learnerId, $bookingId)
    {
        return Booking::with(['tutor.user', 'course', 'bookingSlots.masterSlot', 'review'])
            ->where('learner_id', $learnerId)
            ->where('id', $bookingId)
            ->firstOrFail();
    }

    /**
     * Memproses Pembayaran via Midtrans
     */
    public function payBooking($learnerId, $bookingId, $paymentMethod)
    {
        $booking = Booking::with(['learner', 'tutor.user', 'course'])
            ->where('learner_id', $learnerId)
            ->where('id', $bookingId)
            ->where('payment_status', 'unpaid')
            ->firstOrFail();

        if ($booking->payment_expired_at && now()->greaterThan($booking->payment_expired_at)) {
            $booking->update(['status' => 'cancelled']);
            throw new Exception("Batas waktu pembayaran telah habis. Pesanan otomatis dibatalkan.");
        }

        if ($paymentMethod === 'cash') {
            $booking->update([
                'payment_method' => 'cash',
                'payment_code' => 'CASH-' . $booking->id,
                'status' => 'accepted',
            ]);
        } else {
            // Generate Midtrans Snap Token
            $midtrans = new \App\Services\Payment\MidtransService();
            $snapResponse = $midtrans->createSnapToken($booking);

            $booking->update([
                'payment_method' => 'midtrans',
                'payment_code' => $snapResponse['token'], // Simpan snap token di field payment_code
            ]);
        }

        return $booking->load(['bookingSlots.masterSlot']);
    }

    /**
     * Simulasi Pembayaran Sukses (Ketika klik OK di Pop-Up VA)
     */
    public function simulatePaymentSuccess($learnerId, $bookingId)
    {
        $booking = Booking::with(['learner', 'tutor.user', 'course'])->where('learner_id', $learnerId)
            ->where('id', $bookingId)
            ->firstOrFail();

        if ($booking->payment_expired_at && now()->greaterThan($booking->payment_expired_at)) {
            $booking->update(['status' => 'cancelled']);
            throw new Exception("Batas waktu pembayaran telah habis. Pesanan otomatis dibatalkan.");
        }

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

    /**
     * Submit Ulasan (Review) untuk sebuah pesanan
     */
    public function submitReview($learnerId, $bookingId, $data)
    {
        $booking = Booking::with('tutor')->where('learner_id', $learnerId)
            ->where('id', $bookingId)
            ->where('status', 'completed')
            ->firstOrFail();

        // Cek jika sudah pernah review
        if ($booking->review()->exists()) {
            throw new Exception("Anda sudah memberikan ulasan untuk sesi ini.");
        }

        DB::beginTransaction();
        try {
            // 1. Simpan Ulasan
            $review = $booking->review()->create([
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null
            ]);

            // 2. Update Rata-Rata Rating Tutor
            $tutor = $booking->tutor;
            
            // Hitung rata-rata baru (rumus standar matematika)
            $newTotalReviews = $tutor->total_reviews + 1;
            $newRatingAvg = (($tutor->rating_avg * $tutor->total_reviews) + $data['rating']) / $newTotalReviews;

            $tutor->update([
                'total_reviews' => $newTotalReviews,
                'rating_avg' => round($newRatingAvg, 1)
            ]);

            DB::commit();
            return $review;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Membatalkan pesanan yang belum dibayar
     */
    public function cancelBooking($learnerId, $bookingId)
    {
        $booking = Booking::where('learner_id', $learnerId)
            ->where('id', $bookingId)
            ->firstOrFail();

        if ($booking->payment_status !== 'unpaid' || !in_array($booking->status, ['pending', 'accepted'])) {
            throw new Exception("Pesanan tidak dapat dibatalkan karena status tidak valid atau sudah dibayar.");
        }

        $booking->update([
            'status' => 'cancelled'
        ]);

        return $booking;
    }
}

