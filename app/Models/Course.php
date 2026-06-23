<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['name', 'code', 'semester'];

    // Relasi ke TutorCourse
    public function tutors()
    {
        return $this->hasMany(TutorCourse::class);
    }

    // Relasi ke Booking
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}

