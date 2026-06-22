<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilitySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'day_of_week',
        'slot_id',
        'is_active',
    ];

    // Relasi ke Tutor
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    // Relasi ke MasterSlot (Jam belajarnya)
    public function masterSlot()
    {
        return $this->belongsTo(MasterSlot::class, 'slot_id');
    }
}
