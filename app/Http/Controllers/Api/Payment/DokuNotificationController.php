<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Services\Payment\DokuService;
use App\Services\Admin\PaymentService;
use Illuminate\Support\Facades\Log;

class DokuNotificationController extends Controller
{
    protected $dokuService;
    protected $paymentService;

    public function __construct(DokuService $dokuService, PaymentService $paymentService)
    {
        $this->dokuService = $dokuService;
        $this->paymentService = $paymentService;
    }

    /**
     * Handle incoming DOKU payment webhook
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $headers = $request->headers->all();
        $rawBody = $request->getContent();

        Log::info('DOKU Webhook Received', [
            'headers' => $headers,
            'payload' => $payload
        ]);

        // 1. Verify Signature
        if (!$this->dokuService->verifyNotificationSignature($headers, $rawBody)) {
            Log::warning('DOKU Webhook Invalid Signature');
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 403);
        }

        // 2. Parse Invoice & Booking ID
        $invoiceNumber = $payload['order']['invoice_number'] ?? $payload['order']['invoiceNumber'] ?? '';
        $parts = explode('-', $invoiceNumber);
        $bookingId = $parts[1] ?? null;

        if (!$bookingId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid invoice number format'
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
        $transactionStatus = strtoupper($payload['transaction']['status'] ?? '');
        $paymentType = $payload['channel']['name'] ?? 'doku';

        Log::info("Processing DOKU status for Booking #{$bookingId}: status={$transactionStatus}");

        if ($transactionStatus === 'SUCCESS') {
            $this->paymentService->approvePayment($bookingId);
            $booking->update([
                'payment_method' => $paymentType
            ]);
        } elseif (in_array($transactionStatus, ['FAILED', 'EXPIRED', 'CANCELLED'])) {
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
