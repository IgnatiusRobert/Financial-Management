<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List user's notifications with unread count.
     */
        public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Ambil semua notifikasi tanpa paginate, langsung sebagai List
        $notifications = $user->notifications()->get()->map(function ($notif) {
            return [
                'id'         => $notif->id,
                'type'       => $notif->type,
                'title'      => $notif->data['title'] ?? 'Pemberitahuan',
                'message'    => $notif->data['message'] ?? '',
                'data'       => $notif->data,
                'read_at'    => $notif->read_at,
                'created_at' => $notif->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar notifikasi berhasil diambil.',
            'data'    => $notifications, // ← langsung List, bukan nested Map
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($id);

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai sudah dibaca.',
            'data' => $notification->fresh(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil ditandai sudah dibaca.',
            'data' => null,
        ]);
    }
}
