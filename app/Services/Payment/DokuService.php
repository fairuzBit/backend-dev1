<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DokuService
{
    protected $clientId;
    protected $secretKey;
    protected $isProduction;
    protected $checkoutUrl;

    public function __construct()
    {
        $this->clientId = config('services.doku.client_id');
        $this->secretKey = config('services.doku.secret_key');
        $this->isProduction = (bool) config('services.doku.is_production', false);
        $this->checkoutUrl = $this->isProduction
            ? 'https://api.doku.com/checkout/v1/payment'
            : 'https://api-sandbox.doku.com/checkout/v1/payment';
    }

    /**
     * Create Checkout URL for DOKU payment
     */
    public function createCheckoutUrl($booking)
    {
        if (empty($this->clientId) || empty($this->secretKey)) {
            Log::warning('DOKU Client ID or Secret Key is empty. Simulating payment auto-approval for sandbox testing.');
            try {
                $paymentService = app(\App\Services\Admin\PaymentService::class);
                $paymentService->approvePayment($booking->id);
            } catch (\Exception $e) {
                Log::error('Mock payment approval failed: ' . $e->getMessage());
            }

            $callbackBase = config('services.doku.callback_url');
            $callbackUrl = rtrim($callbackBase, '/') . '/' . $booking->id;

            return [
                'payment' => [
                    'url' => $callbackUrl
                ]
            ];
        }

        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $requestId = (string) Str::uuid();
        $targetPath = '/checkout/v1/payment';

        $callbackBase = config('services.doku.callback_url');
        $callbackUrl = rtrim($callbackBase, '/') . '/' . $booking->id;

        $payload = [
            'order' => [
                'amount' => (int) $booking->grand_total,
                'invoice_number' => 'BOOKING-' . $booking->id . '-' . time(),
                'callback_url' => $callbackUrl,
                'auto_redirect' => true,
            ],
            'customer' => [
                'name' => $booking->learner->name,
                'email' => $booking->learner->email,
                'phone' => $booking->learner->phone ?? '',
            ]
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $digest = base64_encode(hash('sha256', $payloadJson, true));

        $signatureString = "Client-Id:" . $this->clientId . "\n" .
                           "Request-Id:" . $requestId . "\n" .
                           "Request-Timestamp:" . $timestamp . "\n" .
                           "Request-Target:" . $targetPath . "\n" .
                           "Digest:" . $digest;

        $signature = base64_encode(hash_hmac('sha256', $signatureString, $this->secretKey, true));

        $response = Http::withHeaders([
            'Client-Id' => $this->clientId,
            'Request-Id' => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature' => 'HMACSHA256=' . $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->withBody($payloadJson, 'application/json')->post($this->checkoutUrl);

        if ($response->failed()) {
            Log::error('DOKU Checkout Generation Failed', [
                'booking_id' => $booking->id,
                'response' => $response->body()
            ]);

            if (!$this->isProduction) {
                Log::warning('DOKU Checkout failed in Sandbox environment. Simulating payment auto-approval.');
                try {
                    $paymentService = app(\App\Services\Admin\PaymentService::class);
                    $paymentService->approvePayment($booking->id);
                } catch (\Exception $e) {
                    Log::error('Mock payment approval failed: ' . $e->getMessage());
                }

                return [
                    'payment' => [
                        'url' => $callbackUrl
                    ]
                ];
            }

            throw new \Exception('Gagal menghubungi gateway pembayaran DOKU: ' . ($response->json('error_message') ?? 'Unknown Error'));
        }

        return $response->json();
    }

    /**
     * Verify DOKU notification signature key
     */
    public function verifyNotificationSignature($headers, $body)
    {
        if (empty($this->secretKey)) {
            return true;
        }

        $clientId = $headers['client-id'][0] ?? $headers['client-id'] ?? '';
        $requestId = $headers['request-id'][0] ?? $headers['request-id'] ?? '';
        $timestamp = $headers['request-timestamp'][0] ?? $headers['request-timestamp'] ?? '';
        $signature = $headers['signature'][0] ?? $headers['signature'] ?? '';

        $signature = str_replace('HMACSHA256=', '', $signature);
        $targetPath = '/api/payment/notification';
        $digest = base64_encode(hash('sha256', $body, true));

        $signatureString = "Client-Id:" . $clientId . "\n" .
                           "Request-Id:" . $requestId . "\n" .
                           "Request-Timestamp:" . $timestamp . "\n" .
                           "Request-Target:" . $targetPath . "\n" .
                           "Digest:" . $digest;

        $expectedSignature = base64_encode(hash_hmac('sha256', $signatureString, $this->secretKey, true));

        return hash_equals($expectedSignature, $signature);
    }
}
