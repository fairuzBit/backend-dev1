<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorApplication extends Model
{
    use HasFactory;

    // Wajib ditambahkan karena nama tabel tidak berakhiran 's'
    protected $table = 'tutor_application';

    protected $fillable = [
        'user_id',
        'course_id',
        'course_ids',
        'new_semester',
        'grade',
        'transcript_files',
        'portfolio_urls',
        'certificate_files',
        'status',
        'admin_note',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'transcript_files' => 'array',
        'portfolio_urls' => 'array',
        'certificate_files' => 'array',
        'course_ids' => 'array',
        'approved_at' => 'datetime',
    ];

    // Relasi ke User (Yang mendaftar)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Course (Mata kuliah yang diajukan)
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relasi ke Admin yang melakukan approve/reject
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
