<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        /** @var Collection<int, DatabaseNotification> $notifications */
        $notifications = $user->notifications()->latest()->limit(8)->get();

        return response()->json([
            'notifications' => $notifications->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'title' => (string) data_get($notification->data, 'title', 'Notification'),
                'message' => (string) data_get($notification->data, 'message', ''),
                'icon' => (string) data_get($notification->data, 'icon', 'bell'),
                'tone' => (string) data_get($notification->data, 'tone', 'primary'),
                'url' => (string) data_get($notification->data, 'url', route('dashboard.index')),
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->diffForHumans(),
            ])->values(),
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function readAll(Request $request): Response
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->noContent();
    }
}
