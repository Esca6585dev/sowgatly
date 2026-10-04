<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Order;
use App\Models\Shop;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Customer side of the customer <-> shop chat.
 *
 * @OA\Tag(name="Chats", description="Customer chats with shops")
 */
class ChatController extends Controller
{
    use RespondsWithJson;

    public static function threadPayload(ChatThread $thread, string $side): array
    {
        $last = $thread->lastMessage;

        return [
            'id' => $thread->id,
            'shop' => $thread->shop ? [
                'id' => $thread->shop->id,
                'name' => $thread->shop->name,
                'image' => $thread->shop->image ? asset('storage/' . $thread->shop->image) : null,
            ] : null,
            'user' => $thread->user ? [
                'id' => $thread->user->id,
                'name' => $thread->user->name,
                'image' => $thread->user->image ? asset($thread->user->image) : null,
            ] : null,
            'order_id' => $thread->order_id,
            'last_message' => $last ? [
                'body' => $last->body,
                'sender_type' => $last->sender_type,
                'created_at' => $last->created_at,
            ] : null,
            'unread' => $side === 'user' ? $thread->user_unread : $thread->shop_unread,
            'last_message_at' => $thread->last_message_at,
            'created_at' => $thread->created_at,
        ];
    }

    public static function messagesQuery(ChatThread $thread, Request $request)
    {
        $limit = min(max((int) $request->query('limit', 50), 1), 100);
        $query = ChatMessage::where('thread_id', $thread->id);

        if ($request->filled('after')) {
            return $query->where('id', '>', (int) $request->query('after'))->orderBy('id')->limit($limit)->get();
        }

        // Without a cursor: the newest N, returned oldest-first.
        return $query->orderByDesc('id')->limit($limit)->get()->reverse()->values();
    }

    protected function ownThread(Request $request, $id): ?ChatThread
    {
        return ChatThread::with('shop', 'user', 'lastMessage')
            ->where('user_id', $request->user()->id)
            ->find($id);
    }

    /**
     * @OA\Get(
     *     path="/api/me/chats",
     *     summary="The customer's chat threads, most recent first",
     *     tags={"Chats"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Successful operation"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function index(Request $request)
    {
        $threads = ChatThread::with('shop', 'user', 'lastMessage')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();

        return $this->ok([
            'data' => $threads->map(fn ($t) => self::threadPayload($t, 'user'))->values(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/me/chats",
     *     summary="Open (or reuse) a chat with a shop",
     *     tags={"Chats"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(@OA\JsonContent(required={"shop_id"},
     *         @OA\Property(property="shop_id", type="integer", example=3),
     *         @OA\Property(property="order_id", type="integer", nullable=true, example=15)
     *     )),
     *     @OA\Response(response="200", description="Existing thread"),
     *     @OA\Response(response="201", description="Thread created"),
     *     @OA\Response(response="422", description="Validation error"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|integer|exists:shops,id',
            'order_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $user = $request->user();
        $orderId = $request->input('order_id');

        if ($orderId !== null) {
            $order = Order::where('user_id', $user->id)->where('shop_id', $request->input('shop_id'))->find($orderId);
            if (!$order) {
                return $this->fail('Order not found', 404);
            }
        }

        $thread = ChatThread::firstOrCreate([
            'user_id' => $user->id,
            'shop_id' => $request->input('shop_id'),
            'order_id' => $orderId,
        ]);
        $thread->load('shop', 'user', 'lastMessage');

        return $this->ok(['data' => self::threadPayload($thread, 'user')], null, $thread->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * @OA\Get(
     *     path="/api/me/chats/{id}/messages",
     *     summary="Messages of a thread (oldest first)",
     *     tags={"Chats"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="after", in="query", required=false, description="Return only messages with id greater than this", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=50, maximum=100)),
     *     @OA\Response(response="200", description="Successful operation"),
     *     @OA\Response(response="404", description="Thread not found"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function messages(Request $request, $id)
    {
        $thread = $this->ownThread($request, $id);
        if (!$thread) {
            return $this->fail('Chat not found', 404);
        }

        return $this->ok(['data' => self::messagesQuery($thread, $request)]);
    }

    /**
     * @OA\Post(
     *     path="/api/me/chats/{id}/messages",
     *     summary="Send a message to the shop",
     *     tags={"Chats"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(required={"body"}, @OA\Property(property="body", type="string", maxLength=2000))),
     *     @OA\Response(response="201", description="Message sent"),
     *     @OA\Response(response="404", description="Thread not found"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function send(Request $request, $id)
    {
        $thread = $this->ownThread($request, $id);
        if (!$thread) {
            return $this->fail('Chat not found', 404);
        }

        $validator = Validator::make($request->all(), ['body' => 'required|string|max:2000']);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $message = $thread->post('user', $request->user()->id, trim(strip_tags($request->input('body'))));

        // Tell the shop owner in-app; the shop reads the thread from /api/shop/chats.
        if ($thread->shop && $thread->shop->user_id) {
            UserNotification::create([
                'user_id' => $thread->shop->user_id,
                'type' => 'chat_message',
                'data' => ['thread_id' => $thread->id, 'shop_id' => $thread->shop_id, 'message_id' => $message->id],
            ]);
        }

        return $this->ok(['data' => $message], null, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/me/chats/{id}/read",
     *     summary="Mark the shop's messages in this thread as read",
     *     tags={"Chats"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Marked read"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function read(Request $request, $id)
    {
        $thread = $this->ownThread($request, $id);
        if (!$thread) {
            return $this->fail('Chat not found', 404);
        }

        $thread->markReadBy('user');

        return $this->ok();
    }

    /**
     * @OA\Get(
     *     path="/api/me/chats/unread-count",
     *     summary="Total unread chat messages for the customer",
     *     tags={"Chats"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Successful operation", @OA\JsonContent(@OA\Property(property="unread", type="integer"))),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function unreadCount(Request $request)
    {
        return $this->ok([
            'unread' => (int) ChatThread::where('user_id', $request->user()->id)->sum('user_unread'),
        ]);
    }
}
