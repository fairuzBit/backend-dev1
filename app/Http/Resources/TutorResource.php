<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TutorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tutor_id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'nim' => $this->user->nim,
            'phone' => $this->user->phone,
            'avatar' => $this->user->avatar ? (str_starts_with($this->user->avatar, 'data:image') || str_starts_with($this->user->avatar, 'http') ? $this->user->avatar : asset('storage/' . $this->user->avatar)) : null,
            'bio' => $this->bio,
            'ipk' => $this->ipk,
            'is_active' => (bool) $this->is_active,
            'current_semester' => $this->current_semester,
            'rating_avg' => $this->rating_avg,
            'total_reviews' => $this->total_reviews,
            'total_sessions' => $this->total_sessions ?? 0,
            'skills' => is_array($this->skills) ? $this->skills : (is_string($this->skills) ? json_decode($this->skills, true) : []),
            'price' => (int) $this->price,
            'portfolio_urls' => is_array($this->portfolio_urls) ? $this->portfolio_urls : [],
            'certificate_files' => collect($this->certificate_files ?? [])->map(function ($path) {
                return asset('storage/' . $path);
            })->toArray(),
            
            'taught_courses' => $this->whenLoaded('courses', function () {
                return $this->courses->map(function ($tutorCourse) {
                    return [
                        'tutor_course_id' => $tutorCourse->id, 
                        'course_id' => $tutorCourse->course_id,
                        'course_name' => $tutorCourse->course->name,
                        'course_code' => $tutorCourse->course->code,
                        'grade' => $tutorCourse->grade,
                    ];
                });
            }),
            
            'available_slots' => $this->whenLoaded('availabilitySlots', function () {
                return $this->availabilitySlots->filter(fn($avail) => $avail->is_active)->map(function ($avail) {
                    return [
                        'availability_id' => $avail->id,
                        'slot_id' => $avail->slot_id,
                        'day_of_week' => $avail->day_of_week,
                        'start_time' => date('H:i', strtotime($avail->masterSlot->start_time ?? '')),
                        'end_time' => date('H:i', strtotime($avail->masterSlot->end_time ?? '')),
                    ];
                })->values();
            }),
            
            'documents' => $this->when(true, function () {
                $documents = [];
                
                $latestApp = \App\Models\TutorApplication::where('user_id', $this->user_id)
                    ->whereNotNull('transcript_files')
                    ->latest()
                    ->first();

                if ($latestApp && ! empty($latestApp->transcript_files)) {
                    foreach ($latestApp->transcript_files as $path) {
                        $documents[] = [
                            'type' => 'transcript',
                            'label' => 'Transkrip Nilai',
                            'name' => basename($path),
                            'url' => url('storage/'.$path),
                        ];
                    }
                }

                if (! empty($this->certificate_files)) {
                    foreach ($this->certificate_files as $cert) {
                        $documents[] = [
                            'type' => 'certificate',
                            'label' => 'Sertifikat',
                            'name' => basename($cert),
                            'url' => url('storage/'.$cert),
                        ];
                    }
                }

                if (! empty($this->portfolio_urls)) {
                    foreach ($this->portfolio_urls as $port) {
                        $documents[] = [
                            'type' => 'link',
                            'label' => 'Portofolio',
                            'value' => $port,
                            'name' => $port,
                            'url' => $port,
                        ];
                    }
                }
                
                return $documents;
            }),

            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->filter(fn($review) => $review->moderation_status === 'approved' || $review->moderation_status === 'pending')->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at->toIso8601String(),
                        'learner' => [
                            'name' => $review->booking->learner->name ?? 'Learner',
                            'avatar' => $review->booking->learner->avatar ? (str_starts_with($review->booking->learner->avatar, 'data:image') ? $review->booking->learner->avatar : asset('storage/' . $review->booking->learner->avatar)) : null,
                        ],
                    ];
                });
            }),
        ];
    }
}
