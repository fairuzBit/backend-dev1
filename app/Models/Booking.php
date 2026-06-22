<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'course_id',
        'learner_id',
        'booking_date',
        'total_price',
        'service_fee',
        'grand_total',
        'status',
        'payment_status',
        'payment_method',
        'payment_code',
        'payment_expired_at',
    ];

    protected $casts = [
        'payment_expired_at' => 'datetime',
        'booking_date' => 'date',
    ];

    // Relasi ke Tutor yang dibooking
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    // Relasi ke Mata Kuliah
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relasi ke Murid yang memesan (Nyambung ke tabel Users)
    public function learner()
    {
        return $this->belongsTo(User::class, 'learner_id');
    }

    // Relasi ke daftar jam (BookingSlot) di dalam 1 pesanan ini
    public function bookingSlots()
    {
        return $this->hasMany(BookingSlot::class);
    }

    // Relasi ke Ulasan (1 Transaksi punya maksimal 1 Ulasan)
    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
