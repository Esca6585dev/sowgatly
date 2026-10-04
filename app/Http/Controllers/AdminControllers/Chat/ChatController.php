<?php

namespace App\Http\Controllers\AdminControllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatThread;
use Illuminate\Http\Request;

/**
 * Read-only moderation view of customer <-> shop chats.
 */
class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));

        $threads = ChatThread::with('user:id,name,phone_number', 'shop:id,name', 'lastMessage')
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('phone_number', 'like', $like))
                  ->orWhereHas('shop', fn ($s) => $s->where('name', 'like', $like));
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.chat.chat-table', compact('threads', 'pagination'))->render();
        }

        return view('admin-panel.chat.chat', compact('threads', 'pagination'));
    }

    public function show($lang, ChatThread $chat)
    {
        $chat->load('user', 'shop', 'order');
        $messages = $chat->messages()->orderBy('id')->limit(500)->get();

        return view('admin-panel.chat.chat-show', ['thread' => $chat, 'messages' => $messages]);
    }
}
