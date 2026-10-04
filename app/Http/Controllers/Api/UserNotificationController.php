<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/me/notifications",
     *     summary="The caller's last 50 in-app notifications",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Notifications, newest first", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean"),
     *         @OA\Property(property="data", type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="type", type="string", enum={"order_created","order_status","product_available","chat_message","shop_application"}),
     *             @OA\Property(property="data", type="object", description="Keys depend on type, see docs/notifications.md"),
     *             @OA\Property(property="title", type="string", description="Localized by Accept-Language"),
     *             @OA\Property(property="body", type="string"),
     *             @OA\Property(property="read_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )),
     *         @OA\Property(property="meta", type="object", @OA\Property(property="unread", type="integer"))
     *     ))
     * )
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $notifications = UserNotification::where('user_id', $userId)
            ->latest()
            ->limit(50)
            ->get();

        // title/body are additive, localized by Accept-Language (Turkmen default).
        $locale = app()->getLocale();
        $notifications->each(function (UserNotification $n) use ($locale) {
            $n->setAttribute('title', $n->title($locale));
            $n->setAttribute('body', $n->body($locale));
        });

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'meta' => [
                'unread' => UserNotification::where('user_id', $userId)->whereNull('read_at')->count(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/me/notifications/unread-count",
     *     summary="Number of unread in-app notifications",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Successful operation", @OA\JsonContent(@OA\Property(property="unread", type="integer"))),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function unreadCount(Request $request)
    {
        return response()->json([
            'success' => true,
            'unread' => UserNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/me/notifications/read",
     *     summary="Mark every notification as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Done")
     * )
     */
    public function markAllRead(Request $request)
    {
        UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
