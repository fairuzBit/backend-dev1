<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\MasterSlot;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $learner8 = User::where('email', '111dewilestari8@mhs.dinus.ac.id')->first();
        $learner9 = User::where('email', '111agusprayitno9@mhs.dinus.ac.id')->first();
        $learner10 = User::where('email', '111ninakarina10@mhs.dinus.ac.id')->first();

        $tutorUser1 = User::where('email', '111andiwijaya1@mhs.dinus.ac.id')->first();
        $tutorUser2 = User::where('email', '111sitinurhaliza2@mhs.dinus.ac.id')->first();
        $tutorUser3 = User::where('email', '111rezarahadian3@mhs.dinus.ac.id')->first();

        if (!$learner8 || !$tutorUser1 || !$tutorUser2 || !$tutorUser3) return;

        $tutor1 = Tutor::where('user_id', $tutorUser1->id)->first();
        $tutor2 = Tutor::where('user_id', $tutorUser2->id)->first();
        $tutor3 = Tutor::where('user_id', $tutorUser3->id)->first();

        $masterSlots = MasterSlot::take(3)->get(); 
        if ($masterSlots->count() < 3) return;

        // Booking 1: Completed
        $bookingCompleted = Booking::firstOrCreate(
            [
                'learner_id' => $learner8->id,
                'tutor_id' => $tutor1->id,
                'status' => 'completed',
            ],
            [
                'course_id' => $tutor1->courses->first()->course_id ?? 1,
                'booking_date' => Carbon::yesterday()->toDateString(),
                'payment_status' => 'paid',
                'total_price' => $tutor1->price,
                'service_fee' => 1000,
                'grand_total' => $tutor1->price + 1000,
                'payment_method' => 'bank_transfer',
                'payment_code' => 'PAY-COMPLETED-123'
            ]
        );
        
        BookingSlot::firstOrCreate([
            'booking_id' => $bookingCompleted->id,
            'slot_id' => $masterSlots[0]->id,
        ], [
            'start_time' => $masterSlots[0]->start_time,
            'end_time' => $masterSlots[0]->end_time
        ]);

        BookingSlot::firstOrCreate([
            'booking_id' => $bookingCompleted->id,
            'slot_id' => $masterSlots[1]->id,
        ], [
            'start_time' => $masterSlots[1]->start_time,
            'end_time' => $masterSlots[1]->end_time
        ]);

        // Booking 2: Paid/Accepted
        $bookingPaid = Booking::firstOrCreate(
            [
                'learner_id' => $learner9->id,
                'tutor_id' => $tutor2->id,
                'status' => 'accepted',
            ],
            [
                'course_id' => $tutor2->courses->first()->course_id ?? 1,
                'booking_date' => Carbon::tomorrow()->toDateString(),
                'payment_status' => 'paid',
                'total_price' => $tutor2->price,
                'service_fee' => 1000,
                'grand_total' => $tutor2->price + 1000,
                'payment_method' => 'ewallet',
                'payment_code' => 'PAY-PAID-456'
            ]
        );
        
        BookingSlot::firstOrCreate([
            'booking_id' => $bookingPaid->id,
            'slot_id' => $masterSlots[0]->id,
        ], [
            'start_time' => $masterSlots[0]->start_time,
            'end_time' => $masterSlots[0]->end_time
        ]);

        // Booking 3: Pending/Paid
        $bookingPending = Booking::firstOrCreate(
            [
                'learner_id' => $learner10->id,
                'tutor_id' => $tutor3->id,
                'status' => 'pending',
                'payment_status' => 'paid',
            ],
            [
                'course_id' => $tutor3->courses->first()->course_id ?? 1,
                'booking_date' => Carbon::now()->addDays(2)->toDateString(),
                'total_price' => $tutor3->price,
                'service_fee' => 1000,
                'grand_total' => $tutor3->price + 1000,
                'payment_method' => 'qris',
                'payment_code' => 'PAY-PENDING-789'
            ]
        );
        
        BookingSlot::firstOrCreate([
            'booking_id' => $bookingPending->id,
            'slot_id' => $masterSlots[2]->id,
        ], [
            'start_time' => $masterSlots[2]->start_time,
            'end_time' => $masterSlots[2]->end_time
        ]);
        
        // Booking 4: Pending/Unpaid
        $bookingUnpaid = Booking::firstOrCreate(
            [
                'learner_id' => $learner8->id,
                'tutor_id' => $tutor2->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ],
            [
                'course_id' => $tutor2->courses->first()->course_id ?? 1,
                'booking_date' => Carbon::now()->addDays(3)->toDateString(),
                'total_price' => $tutor2->price,
                'service_fee' => 1000,
                'grand_total' => $tutor2->price + 1000,
            ]
        );
        
        BookingSlot::firstOrCreate([
            'booking_id' => $bookingUnpaid->id,
            'slot_id' => $masterSlots[2]->id,
        ], [
            'start_time' => $masterSlots[2]->start_time,
            'end_time' => $masterSlots[2]->end_time
        ]);

        // Booking 5: Rejected
        $bookingRejected = Booking::firstOrCreate(
            [
                'learner_id' => $learner10->id,
                'tutor_id' => $tutor1->id,
                'status' => 'rejected',
            ],
            [
                'course_id' => $tutor1->courses->first()->course_id ?? 1,
                'booking_date' => Carbon::now()->addDays(4)->toDateString(),
                'payment_status' => 'refunded',
                'total_price' => $tutor1->price,
                'service_fee' => 1000,
                'grand_total' => $tutor1->price + 1000,
            ]
        );
        
        BookingSlot::firstOrCreate([
            'booking_id' => $bookingRejected->id,
            'slot_id' => $masterSlots[0]->id,
        ], [
            'start_time' => $masterSlots[0]->start_time,
            'end_time' => $masterSlots[0]->end_time
        ]);

        // Booking 6: Completed (For Testing "Beri Ulasan")
        $bookingForReview = Booking::firstOrCreate(
            [
                'learner_id' => $learner8->id, // Dewi Lestari
                'tutor_id' => $tutor3->id,     // Reza Rahadian
                'status' => 'completed',
            ],
            [
                'course_id' => $tutor3->courses->first()->course_id ?? 1,
                'booking_date' => Carbon::yesterday()->toDateString(),
                'payment_status' => 'paid',
                'total_price' => $tutor3->price,
                'service_fee' => 1000,
                'grand_total' => $tutor3->price + 1000,
                'payment_method' => 'bank_transfer',
                'payment_code' => 'PAY-COMPLETED-REVIEW-TEST'
            ]
        );
        
        BookingSlot::firstOrCreate([
            'booking_id' => $bookingForReview->id,
            'slot_id' => $masterSlots[1]->id,
        ], [
            'start_time' => $masterSlots[1]->start_time,
            'end_time' => $masterSlots[1]->end_time
        ]);
    }
}
