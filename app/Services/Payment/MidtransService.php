<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected $serverKey;
    protected $isProduction;
    protected $snapUrl;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key');
        $this->isProduction = (bool) config('services.midtrans.is_production', false);
        $this->snapUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Create Snap Token and Redirect URL for Midtrans payment
     */
    public function createSnapToken($booking)
    {
        if (empty($this->serverKey)) {
            Log::warning('Midtrans server key is empty. Using mock response for sandbox simulation.');
            return [
                'token' => 'mock-midtrans-snap-token-' . $booking->id . '-' . time(),
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . uniqid()
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => 'BOOKING-' . $booking->id . '-' . time(), // Append time to make it unique per retry
                'gross_amount' => (int) $booking->grand_total,
            ],
            'customer_details' => [
                'first_name' => $booking->learner->name,
                'email' => $booking->learner->email,
                'phone' => $booking->learner->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => 'COURSE-' . $booking->course_id,
                    'price' => (int) $booking->total_price,
                    'quantity' => 1,
                    'name' => substr($booking->course->name ?? 'Course Session', 0, 50),
                ],
                [
                    'id' => 'SERVICE-FEE',
                    'price' => (int) $booking->service_fee,
                    'quantity' => 1,
                    'name' => 'Biaya Layanan',
                ]
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'duration' => 15,
                'unit' => 'minutes'
            ]
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($this->snapUrl, $payload);

        if ($response->failed()) {
            Log::error('Midtrans Snap Generation Failed', [
                'booking_id' => $booking->id,
                'response' => $response->body()
            ]);
            throw new \Exception('Gagal menghubungi gateway pembayaran Midtrans: ' . ($response->json('error_messages.0') ?? 'Unknown Error'));
        }

        return $response->json();
    }

    /**
     * Verify Midtrans notification signature key
     */
    public function verifyNotificationSignature($payload)
    {
        if (empty($this->serverKey)) {
            // Mock signature verification for offline testing/sandbox simulation
            return true;
        }

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        
        $signatureString = $orderId . $statusCode . $grossAmount . $this->serverKey;
        $hash = hash('sha512', $signatureString);

        return $hash === ($payload['signature_key'] ?? '');
    }
}
