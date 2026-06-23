<?php

namespace App\Services\Admin;

use App\Models\TutorApplication;
use App\Models\Notification;

class ApplicationService
{
    public function getAllApplications()
    {
        return TutorApplication::with(['user', 'course'])
                               ->orderBy('created_at', 'desc')
                               ->get();
    }

    public function approveApplication(int $id, int $adminId)
    {
        $app = TutorApplication::with(['user', 'course'])->findOrFail($id);
        $app->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now()
        ]);
        
        if ($app->new_semester !== null && $app->user->tutor) {
            $app->user->tutor->update([
                'current_semester' => $app->new_semester
            ]);
            $title = 'Pengajuan Naik Semester Disetujui';
            $message = "Selamat! Pengajuan Anda untuk naik ke semester {$app->new_semester} telah disetujui. Anda kini bisa mengajar mata kuliah yang lebih tinggi.";
        } else {
            $courseName = $app->course ? $app->course->name : '';
            $title = 'Selamat! Anda Menjadi Tutor';
            $message = "Selamat! Pengajuan Anda sebagai tutor mata kuliah {$courseName} telah disetujui Admin. Sekarang Anda resmi menjadi Tutor di KonekDin.";
        }

        if ($app->portfolio_urls && $app->user->tutor) {
            $app->user->tutor->update([
                'portfolio_urls' => $app->portfolio_urls
            ]);
        }

        if ($app->course_id && $app->user->tutor) {
            \App\Models\TutorCourse::firstOrCreate([
                'tutor_id' => $app->user->tutor->id,
                'course_id' => $app->course_id,
            ], [
                'grade' => $app->grade ?? 'N/A'
            ]);
        }

        Notification::create([
            'user_id' => $app->user_id,
            'role' => 'learner',
            'type' => 'application',
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);

        $app->user->assignRole('tutor');
        return $app;
    }

    public function rejectApplication(int $id, ?string $reason = null, ?int $adminId = null)
    {
        $app = TutorApplication::with(['user', 'course'])->findOrFail($id);
        $app->update([
            'status' => 'rejected',
            'admin_note' => $reason,
            'approved_by' => $adminId // Mencatat admin siapa yang menolak
        ]);

        $title = $app->new_semester !== null ? 'Pengajuan Naik Semester Ditolak' : 'Pengajuan Tutor Ditolak';
        $reasonText = $reason ? " Catatan Admin: {$reason}" : " Mohon perbaiki dokumen Anda dan ajukan ulang.";
        
        $courseName = $app->course ? $app->course->name : '';
        if ($app->new_semester !== null) {
            $message = "Mohon maaf, pengajuan naik semester Anda untuk naik ke semester {$app->new_semester} ditolak.{$reasonText}";
        } else {
            $message = "Mohon maaf, pengajuan Anda sebagai tutor mata kuliah {$courseName} ditolak.{$reasonText}";
        }
        
        Notification::create([
            'user_id' => $app->user_id,
            'role' => 'learner',
            'type' => 'application',
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);

        return $app;
    }

    public function deleteApplication(int $id)
    {
        $app = TutorApplication::findOrFail($id);
        
        if ($app->transcript_files && is_array($app->transcript_files)) {
            foreach ($app->transcript_files as $path) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                }
            }
        }

        if ($app->certificate_files && is_array($app->certificate_files)) {
            foreach ($app->certificate_files as $path) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                }
            }
        }
        
        $app->delete();
        return true;
    }
}
