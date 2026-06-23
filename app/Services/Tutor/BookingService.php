<?php

namespace App\Services\Tutor;

use App\Models\Booking;
use Exception;

class BookingService
{
    public function getPendingBookings(int $tutorId)
    {
        return Booking::with(['learner', 'bookingSlots.masterSlot'])
            ->where('tutor_id', $tutorId)
            ->where('status', 'pending')
            ->where('payment_status', 'paid')
            ->get();
    }

    public function getSchedules(int $tutorId)
    {
        return Booking::with(['learner', 'bookingSlots.masterSlot'])
            ->where('tutor_id', $tutorId)
            ->where('status', 'accepted')
            ->where('booking_date', '>=', now()->toDateString())
            ->get();
    }

    public function getHistory(int $tutorId)
    {
        return Booking::with(['learner', 'course', 'bookingSlots.masterSlot', 'review'])
            ->where('tutor_id', $tutorId)
            ->whereIn('status', ['completed', 'rejected', 'cancelled'])
            ->orderBy('booking_date', 'desc')
            ->get();
    }

    public function acceptBooking(int $tutorId, int $bookingId)
    {
        $booking = Booking::where('id', $bookingId)->where('tutor_id', $tutorId)->firstOrFail();
        $booking->update(['status' => 'accepted']);
        return $booking;
    }

    public function rejectBooking(int $tutorId, int $bookingId)
    {
        $booking = Booking::where('id', $bookingId)->where('tutor_id', $tutorId)->firstOrFail();
        $booking->update(['status' => 'rejected']);
        return $booking;
    }

    public function completeBooking(int $tutorId, int $bookingId)
    {
        $booking = Booking::where('id', $bookingId)->where('tutor_id', $tutorId)->firstOrFail();
        $booking->update(['status' => 'completed']);
        return $booking;
    }
}
