<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSlot extends Model
{
    use HasFactory;

    // Menentukan kolom mana saja yang boleh diisi langsung (lewat seeder/form)
    protected $fillable = [
        'code',
        'start_time',
        'end_time',
    ];

    // (Opsional) Jika nanti butuh narik data ketersediaan Tutor berdasarkan Slot
    public function availabilitySlots()
    {
        return $this->hasMany(AvailabilitySlot::class, 'slot_id');
    }

    // Relasi ke BookingSlot
    public function bookingSlots()
    {
        return $this->hasMany(BookingSlot::class, 'slot_id');
    }
}
