<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Booking;
use App\Models\User;
use App\Models\Tutor;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $learner7 = User::where('email', '111rinamelati7@mhs.dinus.ac.id')->first();
        $tutorUser2 = User::where('email', '111sitinurhaliza2@mhs.dinus.ac.id')->first();
        
        if (!$learner7 || !$tutorUser2) return;

        $tutor2 = Tutor::where('user_id', $tutorUser2->id)->first();
        if (!$tutor2) return;

        $courseId = $tutor2->courses->first()->course_id ?? 1;

        $booking1 = Booking::firstOrCreate(
            [
                'learner_id' => $learner7->id,
                'tutor_id' => $tutor2->id,
                'payment_code' => 'PAY-COMPLETED-DUMMY-1'
            ],
            [
                'course_id' => $courseId,
                'booking_date' => now()->subDays(1)->toDateString(),
                'status' => 'completed',
                'payment_status' => 'paid',
                'total_price' => $tutor2->price,
                'service_fee' => 1000,
                'grand_total' => $tutor2->price + 1000,
                'payment_method' => 'bank_transfer',
            ]
        );

        $booking2 = Booking::firstOrCreate(
            [
                'learner_id' => $learner7->id,
                'tutor_id' => $tutor2->id,
                'payment_code' => 'PAY-COMPLETED-DUMMY-2'
            ],
            [
                'course_id' => $courseId,
                'booking_date' => now()->subDays(2)->toDateString(),
                'status' => 'completed',
                'payment_status' => 'paid',
                'total_price' => $tutor2->price,
                'service_fee' => 1000,
                'grand_total' => $tutor2->price + 1000,
                'payment_method' => 'bank_transfer',
            ]
        );

        $booking3 = Booking::firstOrCreate(
            [
                'learner_id' => $learner7->id,
                'tutor_id' => $tutor2->id,
                'payment_code' => 'PAY-COMPLETED-DUMMY-3'
            ],
            [
                'course_id' => $courseId,
                'booking_date' => now()->subDays(3)->toDateString(),
                'status' => 'completed',
                'payment_status' => 'paid',
                'total_price' => $tutor2->price,
                'service_fee' => 1000,
                'grand_total' => $tutor2->price + 1000,
                'payment_method' => 'bank_transfer',
            ]
        );

        $booking4 = Booking::firstOrCreate(
            [
                'learner_id' => $learner7->id,
                'tutor_id' => $tutor2->id,
                'payment_code' => 'PAY-COMPLETED-DUMMY-4'
            ],
            [
                'course_id' => $courseId,
                'booking_date' => now()->subDays(4)->toDateString(),
                'status' => 'completed',
                'payment_status' => 'paid',
                'total_price' => $tutor2->price,
                'service_fee' => 1000,
                'grand_total' => $tutor2->price + 1000,
                'payment_method' => 'bank_transfer',
            ]
        );

        Review::firstOrCreate([
            'booking_id' => $booking1->id,
        ], [
            'rating' => 5,
            'comment' => 'Tutornya sangat pintar dan menjelaskan dengan sangat jelas! Sangat direkomendasikan.',
            'moderation_status' => null
        ]);

        Review::firstOrCreate([
            'booking_id' => $booking2->id,
        ], [
            'rating' => 4,
            'comment' => 'Bagus, tapi kadang koneksi tutor putus-putus saat mengajar online.',
            'moderation_status' => null
        ]);

        Review::firstOrCreate([
            'booking_id' => $booking3->id,
        ], [
            'rating' => 1,
            'comment' => 'Tutor tidak datang tepat waktu dan materinya membingungkan! Tolong refund.',
            'moderation_status' => 'MENUNGGU TINJAUAN'
        ]);

        Review::firstOrCreate([
            'booking_id' => $booking4->id,
        ], [
            'rating' => 2,
            'comment' => 'Kurang memuaskan, tutor terlihat kurang persiapan sebelum mengajar.',
            'moderation_status' => 'MENUNGGU TINJAUAN'
        ]);

        $tutor2->update([
            'rating_avg' => Review::whereHas('booking', function($q) use ($tutor2) {
                $q->where('tutor_id', $tutor2->id);
            })->avg('rating'),
            'total_reviews' => Review::whereHas('booking', function($q) use ($tutor2) {
                $q->where('tutor_id', $tutor2->id);
            })->count()
        ]);
    }
}
