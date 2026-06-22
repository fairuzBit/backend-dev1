<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Learner\TutorDiscoveryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Learner\ProfileController;
use App\Http\Controllers\Api\Learner\DashboardController;
use App\Http\Controllers\Api\Learner\BookingController;
use App\Http\Controllers\Api\Learner\NotificationController;
use App\Http\Controllers\Api\Tutor\TutorRegistrationController;
use App\Http\Controllers\Api\Tutor\DashboardController as TutorDashboardController;
use App\Http\Controllers\Api\Tutor\AvailabilityController;
use App\Http\Controllers\Api\Tutor\BookingController as TutorBookingController;
use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\Tutor\ProfileController as TutorProfileController;
use App\Http\Controllers\Api\Tutor\ReviewController;
use App\Http\Controllers\Api\Tutor\NotificationController as TutorNotificationController;
use App\Http\Controllers\Api\Admin\StatsController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ApplicationController;
use App\Http\Controllers\Api\Admin\MasterDataController as AdminMasterDataController;
use App\Http\Controllers\Api\Admin\PaymentController;
use App\Http\Controllers\Api\Admin\ModerationController;



// ROUTE PUBLIK 
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/supabase-login', [AuthController::class, 'supabaseLogin']);

use App\Http\Controllers\Api\MasterDataController;

// ROUTE PRIVATE
Route::middleware(['auth:api', 'suspended'])->group(function () {
    
    // Fitur User Terautentikasi
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']); 
    
    // Pendaftaran Tutor
    Route::post('/register/tutor/upload-document', [TutorRegistrationController::class, 'uploadDocument']);
    Route::post('/tutor/upgrade-semester', [TutorRegistrationController::class, 'upgradeSemester']);
    
    // Master Data & Public Resources (Bisa diakses Semua Role yang Login)
    Route::get('/courses', [MasterDataController::class, 'courses']);
    Route::get('/master-slots', [MasterDataController::class, 'masterSlots']);
    Route::get('/tutors', [TutorDiscoveryController::class, 'index']); 
    Route::get('/tutors/{id}', [TutorDiscoveryController::class, 'show']);
    
    // KHUSUS ROLE: LEARNER
    Route::middleware('role:learner|tutor,api')->group(function () {
        Route::get('/me', [ProfileController::class, 'me']);
        Route::patch('/me', [ProfileController::class, 'update']);
        Route::get('/me/tutor-application-status', [ProfileController::class, 'tutorApplicationStatus']);
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/learner/bookings', [BookingController::class, 'index']); // Menu Detail Pesanan
        Route::post('/learner/bookings', [BookingController::class, 'store']); // Memesan sesi
        Route::get('/learner/bookings/{id}', [BookingController::class, 'show']); // Lihat 1 pesanan
        Route::patch('/learner/bookings/{id}/pay', [BookingController::class, 'pay']); // Bayar pesanan
        Route::patch('/learner/bookings/{id}/simulate-payment', [BookingController::class, 'simulatePaymentSuccess']); // Klik OK di pop-up VA
        Route::post('/learner/bookings/{id}/reviews', [BookingController::class, 'submitReview']); // Beri Ulasan
        Route::patch('/learner/bookings/{id}/cancel', [BookingController::class, 'cancel']); // Batalkan pesanan
        Route::get('/schedules', [BookingController::class, 'schedules']);
        Route::get('/learner/history', [BookingController::class, 'history']);
        Route::get('/learner/notification', [NotificationController::class, 'index']);
        Route::patch('/learner/notification/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/learner/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    });
    
    // KHUSUS ROLE: TUTOR
    Route::middleware('role:tutor,api')->group(function () {
        Route::get('/tutor/dashboard', [TutorDashboardController::class, 'index']);
        
        Route::get('/tutor/availability', [AvailabilityController::class, 'index']);
        Route::post('/tutor/availability', [AvailabilityController::class, 'store']);
        
        Route::get('/tutor/bookings', [TutorBookingController::class, 'index']);
        Route::patch('/tutor/bookings/{id}/complete', [TutorBookingController::class, 'complete']);

        Route::get('/tutor/history', [TutorBookingController::class, 'history']);
        
        Route::get('/tutor/reviews/summary', [ReviewController::class, 'summary']);
        Route::get('/tutor/reviews', [ReviewController::class, 'index']);
        Route::get('/tutor/notifications', [TutorNotificationController::class, 'index']);
        Route::post('/tutor/notifications/read-all', [TutorNotificationController::class, 'markAllAsRead']);
        
        Route::get('/tutor/profile', [TutorProfileController::class, 'me']);
        Route::patch('/tutor/profile', [TutorProfileController::class, 'update']);
        Route::patch('/tutor/profile/status', [TutorProfileController::class, 'toggleStatus']);
    });
    
    // KHUSUS ROLE: ADMIN
    Route::middleware('role:admin,api')->group(function () {
        // Admin Dashboard Stats
        Route::get('/admin/stats', [StatsController::class, 'index']);
        
        // User Management (Suspend/Unsuspend)
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::get('/admin/users/{id}', [UserController::class, 'show']);
        Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
        Route::patch('/admin/users/{id}/suspend', [UserController::class, 'suspend']);
        Route::patch('/admin/users/{id}/unsuspend', [UserController::class, 'unsuspend']);
        
        Route::get('/admin/applications', [ApplicationController::class, 'index']);
        Route::patch('/admin/applications/{id}/approve', [ApplicationController::class, 'approve']);
        Route::patch('/admin/applications/{id}/reject', [ApplicationController::class, 'reject']);
        Route::delete('/admin/applications/{id}', [ApplicationController::class, 'destroy']);
        
        // Moderation
        Route::get('/admin/moderation/reviews', [ModerationController::class, 'reviews']);
        Route::delete('/admin/moderation/reviews/{id}', [ModerationController::class, 'destroyReview']);
        Route::patch('/admin/moderation/reviews/{id}/process', [ModerationController::class, 'processReview']);
        Route::patch('/admin/moderation/reviews/{id}/resolve', [ModerationController::class, 'resolveReview']);
        Route::get('/admin/moderation/logs', [ModerationController::class, 'logs']);

        // Payments
        Route::get('/admin/payments', [PaymentController::class, 'index']);
        Route::patch('/admin/payments/{id}/approve', [PaymentController::class, 'approve']);
        
        // Admin Booking Moderation
        Route::patch('/admin/bookings/{id}/accept', [AdminBookingController::class, 'accept']);
        Route::patch('/admin/bookings/{id}/reject', [AdminBookingController::class, 'reject']);
        
        // CRUD Master Data
        Route::post('/admin/courses', [AdminMasterDataController::class, 'storeCourse']);
        Route::put('/admin/courses/{id}', [AdminMasterDataController::class, 'updateCourse']);
        Route::delete('/admin/courses/{id}', [AdminMasterDataController::class, 'destroyCourse']);
        Route::post('/admin/master-slots', [AdminMasterDataController::class, 'storeSlot']);
        Route::delete('/admin/master-slots/{id}', [AdminMasterDataController::class, 'destroySlot']);
    });
});
