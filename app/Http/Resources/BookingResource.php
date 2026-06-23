<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_date' => $this->booking_date,
            'total_price' => (int) $this->total_price, // Harga tutor saja
            'service_fee' => (int) $this->service_fee, // Biaya layanan
            'grand_total' => (int) $this->grand_total, // Harga total
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'payment_code' => str_contains($this->payment_code ?? '', ':::') 
                ? explode(':::', $this->payment_code)[0] 
                : $this->payment_code,
            'payment_expired_at' => $this->payment_expired_at ? $this->payment_expired_at->toIso8601String() : null,
            
            // Relasi ke Tutor (Memanggil resource Tutor yang sudah Bos punya)
            'tutor' => new TutorResource($this->whenLoaded('tutor')),
            
            // Relasi ke Course (Kita format manual di sini agar tidak perlu buat CourseResource)
            'course' => $this->whenLoaded('course', function () {
                return [
                    'id' => $this->course->id,
                    'name' => $this->course->name,
                    'code' => $this->course->code,
                ];
            }),
            
            // Relasi ke Jam/Slot
            'slots' => $this->whenLoaded('bookingSlots', function () {
                return $this->bookingSlots->map(function ($bs) {
                    return [
                        'id' => $bs->id,
                        'slot_id' => $bs->slot_id,
                        'start_time' => date('H:i', strtotime($bs->start_time)), // Menghapus detik, dari 08:00:00 jadi 08:00
                        'end_time' => date('H:i', strtotime($bs->end_time)),
                        'code' => $bs->masterSlot->code ?? null, // Mengambil nama sesi dari tabel MasterSlot
                    ];
                });
            }),
            // Relasi ke Ulasan (Review) jika ada
            'review' => $this->whenLoaded('review', function () {
                return $this->review ? [
                    'id' => $this->review->id,
                    'rating' => $this->review->rating,
                    'comment' => $this->review->comment,
                ] : null;
            }),
        ];
    }
}
