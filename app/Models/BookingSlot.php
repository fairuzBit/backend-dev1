<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSlot extends Model
{
    use HasFactory;

    // Wajib ada karena tabel ini tidak punya created_at & updated_at
    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'slot_id',
        'start_time',
        'end_time',
    ];

    // Relasi balik ke Struk Utamanya (Booking)
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Relasi ke tabel Master Jam
    public function masterSlot()
    {
        return $this->belongsTo(MasterSlot::class, 'slot_id');
    }
}
