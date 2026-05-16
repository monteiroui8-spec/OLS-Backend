<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->when($request->get('status') === 'unread', function ($q) {
                $q->whereNull('read_at');
            })
            ->orderByDesc('created_at')
            ->paginate($request->integer('limit', 20));

        $data = $notifications->map(function ($n) {
            return [
                'id' => $n->id,
                'type' => $n->data['type'] ?? null,
                'title' => $n->data['title'] ?? null,
                'body' => $n->data['body'] ?? null,
                'data' => $n->data,
                'readAt' => $n->read_at,
                'createdAt' => $n->created_at,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $notifications->total(),
                'page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'unreadCount' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'message' => 'Notificação marcada como lida.',
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'Todas as notificações marcadas como lidas.',
        ]);
    }
}

