<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorCourse extends Model
{
    protected $fillable = ['tutor_id', 'course_id', 'price'];

    // Menghubungkan harga dengan mata kuliahnya
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Menghubungkan harga dengan tutornya
    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }
}

