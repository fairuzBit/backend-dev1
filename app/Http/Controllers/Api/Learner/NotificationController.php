<?php

namespace App\Http\Controllers\Api\Learner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Learner\NotificationService;
use App\Http\Resources\NotificationResource;

/**
 * @tags Learner - Notification
 */
class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $notifications = $this->notificationService->getUserNotifications($request->user()->id);
            
        return response()->json([
            'success' => true,
            'message' => 'Daftar notifikasi berhasil diambil',
            'data' => NotificationResource::collection($notifications)
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $this->notificationService->markAsRead($request->user()->id, $id);
        
        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $this->notificationService->markAllAsRead($request->user()->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sudah dibaca'
        ]);
    }
}
