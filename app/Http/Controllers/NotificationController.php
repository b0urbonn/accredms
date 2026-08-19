<?php

namespace App\Http\Controllers;

use App\Models\AdditionalDocumentRequest;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        $requestIds = $notifications->pluck('data.request_id')->filter()->unique();
        $documentRequests = AdditionalDocumentRequest::whereIn('id', $requestIds)
            ->with(['requester', 'subfolder.parameterCategory.parameter.area'])
            ->get()
            ->keyBy('id');

        return view('notifications.index', compact('notifications', 'documentRequests'));
    }

    public function unread(Request $request)
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->take(8)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notification',
                'message' => $notification->data['message'] ?? '',
                'area_id' => $notification->data['area_id'] ?? null,
            ]);

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, string $notification)
    {
        $record = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $record->markAsRead();

        return response()->noContent();
    }
}