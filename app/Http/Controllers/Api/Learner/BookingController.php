<?php

namespace App\Http\Controllers\Api\Learner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Learner\BookingService;
use App\Http\Requests\Learner\StoreBookingRequest;
use App\Http\Requests\Learner\PayBookingRequest;
use App\Http\Requests\Learner\SubmitReviewRequest;
use App\Http\Resources\BookingResource;

use Exception;

/**
 * @tags Learner - Booking & Schedule
 */
class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    // Endpoint: GET /api/learner/bookings (Menu: Detail Pesanan)
    public function index(Request $request)
    {
        $orders = $this->bookingService->getActiveOrders($request->user()->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar pesanan aktif berhasil diambil',
            'data' => BookingResource::collection($orders)
        ]);
    }

    // Endpoint: POST /api/learner/bookings
    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking($request->user()->id, $request->all());
            
            return response()->json([
        'success' => true,
        'message' => 'Berhasil memesan jadwal',
        'data' => new BookingResource($booking)
    ], 201);
            
        } catch (Exception $e) {
            // Menangkap lemparan error dari Service (misal karena jadwal bentrok)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Endpoint: GET /api/schedules
    public function schedules(Request $request)
    {
        $schedules = $this->bookingService->getUpcomingSchedules($request->user()->id);
        
         return response()->json([
        'success' => true,
        'message' => 'Jadwal aktif berhasil diambil',
        'data' => BookingResource::collection($schedules)
    ]);
    }

    public function history(Request $request)
    {
        $history = $this->bookingService->getHistory($request->user()->id);
        
        return response()->json([
        'success' => true,
        'message' => 'Riwayat berhasil diambil',
        'data' => BookingResource::collection($history)
    ]);
    }

    // Endpoint: GET /api/learner/bookings/{id}
    public function show(Request $request, $id)
    {
        $booking = $this->bookingService->getBookingDetail($request->user()->id, $id);

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil',
            'data' => new BookingResource($booking)
        ]);
    }

    // Endpoint: PATCH /api/learner/bookings/{id}/pay
    public function pay(PayBookingRequest $request, $id)
    {
        try {
            $booking = $this->bookingService->payBooking($request->user()->id, $id, $request->payment_method);
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi',
                'data' => new BookingResource($booking)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran. Pesanan mungkin sudah lunas atau tidak ditemukan.'
            ], 400);
        }
    }

    // Endpoint: PATCH /api/learner/bookings/{id}/simulate-payment
    public function simulatePaymentSuccess(Request $request, $id)
    {
        try {
            $booking = $this->bookingService->simulatePaymentSuccess($request->user()->id, $id);
            
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi secara sistem (Simulasi)',
                'data' => new BookingResource($booking)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengkonfirmasi pembayaran.'
            ], 400);
        }
    }

    // Endpoint: POST /api/learner/bookings/{id}/reviews
    public function submitReview(SubmitReviewRequest $request, $id)
    {
        try {
            $review = $this->bookingService->submitReview($request->user()->id, $id, $request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Ulasan berhasil dikirim. Terima kasih!',
                'data' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Endpoint: PATCH /api/learner/bookings/{id}/cancel
    public function cancel(Request $request, $id)
    {
        try {
            $booking = $this->bookingService->cancelBooking($request->user()->id, $id);
            
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan',
                'data' => new BookingResource($booking)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

