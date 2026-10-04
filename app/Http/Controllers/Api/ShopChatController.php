<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\ChatThread;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Shop side of the chat: the signed-in user must own a shop.
 *
 * @OA\Tag(name="Shop chats", description="Chats of the signed-in user's shop")
 */
class ShopChatController extends Controller
{
    use RespondsWithJson;

    protected function shopOrFail(Request $request)
    {
        return $request->user()->shop;
    }

    protected function shopThread(Request $request, $id): ?ChatThread
    {
        $shop = $this->shopOrFail($request);
        if (!$shop) {
            return null;
        }

        return ChatThread::with('shop', 'user', 'lastMessage')->where('shop_id', $shop->id)->find($id);
    }

    /**
     * @OA\Get(path="/api/shop/chats", summary="Threads of my shop", tags={"Shop chats"}, security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Successful operation"),
     *     @OA\Response(response="403", description="User has no shop"))
     */
    public function index(Request $request)
    {
        $shop = $this->shopOrFail($request);
        if (!$shop) {
            return $this->fail('You do not have a shop', 403);
        }

        $threads = ChatThread::with('shop', 'user', 'lastMessage')
            ->where('shop_id', $shop->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();

        return $this->ok([
            'data' => $threads->map(fn ($t) => ChatController::threadPayload($t, 'shop'))->values(),
        ]);
    }

    /**
     * @OA\Get(path="/api/shop/chats/{id}/messages", summary="Messages of a thread", tags={"Shop chats"}, security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="after", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=50)),
     *     @OA\Response(response="200", description="Successful operation"),
     *     @OA\Response(response="404", description="Thread not found"))
     */
    public function messages(Request $request, $id)
    {
        $thread = $this->shopThread($request, $id);
        if (!$thread) {
            return $this->fail('Chat not found', 404);
        }

        return $this->ok(['data' => ChatController::messagesQuery($thread, $request)]);
    }

    /**
     * @OA\Post(path="/api/shop/chats/{id}/messages", summary="Reply to the customer", tags={"Shop chats"}, security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(required={"body"}, @OA\Property(property="body", type="string", maxLength=2000))),
     *     @OA\Response(response="201", description="Message sent"),
     *     @OA\Response(response="404", description="Thread not found"))
     */
    public function send(Request $request, $id)
    {
        $thread = $this->shopThread($request, $id);
        if (!$thread) {
            return $this->fail('Chat not found', 404);
        }

        $validator = Validator::make($request->all(), ['body' => 'required|string|max:2000']);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $message = $thread->post('shop', $thread->shop_id, trim(strip_tags($request->input('body'))));

        UserNotification::create([
            'user_id' => $thread->user_id,
            'type' => 'chat_message',
            'data' => ['thread_id' => $thread->id, 'shop_id' => $thread->shop_id, 'message_id' => $message->id],
        ]);

        return $this->ok(['data' => $message], null, 201);
    }

    /**
     * @OA\Post(path="/api/shop/chats/{id}/read", summary="Mark the customer's messages as read", tags={"Shop chats"}, security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Marked read"))
     */
    public function read(Request $request, $id)
    {
        $thread = $this->shopThread($request, $id);
        if (!$thread) {
            return $this->fail('Chat not found', 404);
        }

        $thread->markReadBy('shop');

        return $this->ok();
    }

    /**
     * @OA\Get(path="/api/shop/chats/unread-count", summary="Unread messages for my shop", tags={"Shop chats"}, security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Successful operation"))
     */
    public function unreadCount(Request $request)
    {
        $shop = $this->shopOrFail($request);

        return $this->ok([
            'unread' => $shop ? (int) ChatThread::where('shop_id', $shop->id)->sum('shop_unread') : 0,
        ]);
    }
}
