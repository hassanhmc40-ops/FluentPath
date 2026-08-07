<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    /**
     * List the authenticated student's notifications.
     *
     * Newest first, paginated.
     *
     * @group Notifications
     *
     * @apiResource App\Http\Resources\NotificationResource
     *
     * @apiResourceModel App\Models\Notification
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate();

        return NotificationResource::collection($notifications);
    }

    /**
     * Mark a notification as read.
     *
     * @group Notifications
     *
     * @urlParam notification integer required The notification id. Example: 1
     *
     * @apiResource App\Http\Resources\NotificationResource
     *
     * @apiResourceModel App\Models\Notification
     */
    public function markAsRead(Notification $notification): NotificationResource
    {
        $this->authorize('update', $notification);

        $notification->update(['is_read' => true]);

        return new NotificationResource($notification);
    }
}
