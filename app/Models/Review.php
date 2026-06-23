<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'rating',
        'comment',
        'moderation_status',
        'admin_note',
    ];

    // Relasi ke Transaksi/Booking yang sedang di-review
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
