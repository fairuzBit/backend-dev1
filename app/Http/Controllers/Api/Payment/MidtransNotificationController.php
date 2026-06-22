<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Services\Payment\MidtransService;
use App\Services\Admin\PaymentService;
use Illuminate\Support\Facades\Log;

class MidtransNotificationController extends Controller
{
    protected $midtransService;
    protected $paymentService;

    public function __construct(MidtransService $midtransService, PaymentService $paymentService)
    {
        $this->midtransService = $midtransService;
        $this->paymentService = $paymentService;
    }

    /**
     * Handle incoming Midtrans payment webhook
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Received', $payload);

        // 1. Verify Signature
        if (!$this->midtransService->verifyNotificationSignature($payload)) {
            Log::warning('Midtrans Webhook Invalid Signature', $payload);
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 403);
        }

        // 2. Parse Booking ID
        $orderId = $payload['order_id'] ?? '';
        $parts = explode('-', $orderId);
        $bookingId = $parts[1] ?? null;

        if (!$bookingId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid order ID format'
            ], 400);
        }

        $booking = Booking::find($bookingId);
        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // 3. Process status
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';
        $paymentType = $payload['payment_type'] ?? '';

        Log::info("Processing Midtrans transaction status for Booking #{$bookingId}: status={$transactionStatus}, fraud={$fraudStatus}");

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'challenge') {
                $booking->update([
                    'payment_status' => 'pending',
                    'payment_method' => $paymentType
                ]);
            } else if ($fraudStatus === 'accept') {
                $this->paymentService->approvePayment($bookingId);
                $booking->update([
                    'payment_method' => $paymentType
                ]);
            }
        } else if ($transactionStatus === 'settlement') {
            $this->paymentService->approvePayment($bookingId);
            $booking->update([
                'payment_method' => $paymentType
            ]);
        } else if (in_array($transactionStatus, ['pending'])) {
            $booking->update([
                'payment_status' => 'pending',
                'payment_method' => $paymentType
            ]);
        } else if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $booking->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
                'payment_method' => $paymentType
            ]);

            \App\Models\Notification::create([
                'user_id' => $booking->learner_id,
                'role' => 'learner',
                'type' => 'payment',
                'title' => 'Pembayaran Gagal / Expired',
                'message' => "Pesanan belajar Anda #{$bookingId} telah dibatalkan karena pembayaran expired, gagal, atau ditolak.",
                'is_read' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification processed successfully'
        ]);
    }
}
