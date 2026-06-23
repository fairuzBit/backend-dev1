<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

/**
 * @tags Admin Booking Moderation
 */
class BookingController extends Controller
{
    /**
     * Admin Menerima Pesanan
     */
    public function accept(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Hanya pesanan pending yang bisa di-accept'], 400);
        }

        $booking->update([
            'status' => 'accepted'
        ]);

        return response()->json([
            'message' => 'Pesanan berhasil disetujui oleh Admin',
            'data' => $booking
        ]);
    }

    /**
     * Admin Menolak Pesanan
     */
    public function reject(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Hanya pesanan pending yang bisa di-reject'], 400);
        }

        $booking->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'message' => 'Pesanan berhasil ditolak oleh Admin',
            'data' => $booking
        ]);
    }
}
