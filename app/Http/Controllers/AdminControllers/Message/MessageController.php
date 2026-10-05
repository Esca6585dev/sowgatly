<?php

namespace App\Http\Controllers\AdminControllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Contact messages sent from the website. Admins read and delete them; they
 * are never written from the panel, so create/edit redirect to the list.
 */
class MessageController extends Controller
{
    public const SEARCHABLE = ['username', 'email', 'phone_number', 'message'];

    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));

        $messages = Message::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    foreach (self::SEARCHABLE as $field) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                });
            })
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.message.message-table', compact('messages', 'pagination'));
        }

        return view('admin-panel.message.message', compact('messages', 'pagination'));
    }

    public function create($lang)
    {
        return $this->toIndex();
    }

    public function store($lang)
    {
        return $this->toIndex();
    }

    public function show($lang, Message $message)
    {
        // Message::user() is not a usable relation (wrong keys), so look the sender up directly.
        $sender = $message->user;

        return view('admin-panel.message.message-show', compact('message', 'sender'));
    }

    public function edit($lang, Message $message)
    {
        return $this->toIndex();
    }

    public function update($lang, Message $message)
    {
        return $this->toIndex();
    }

    public function destroy($lang, Message $message)
    {
        $message->delete();

        return redirect()->route('message.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }

    /** Messages come from the website contact form only. */
    private function toIndex()
    {
        return redirect()->route('message.index', app()->getLocale())
            ->with('info', 'Messages are sent from the website contact form and cannot be created or edited here.');
    }
}
