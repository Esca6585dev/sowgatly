<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatThread extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'shop_id', 'order_id', 'last_message_at', 'user_unread', 'shop_unread'];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'thread_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'thread_id')->latestOfMany();
    }

    /**
     * Append a message from one side and bump the other side's unread counter.
     */
    public function post(string $senderType, int $senderId, string $body): ChatMessage
    {
        $message = $this->messages()->create([
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'body' => $body,
        ]);

        $counter = $senderType === 'user' ? 'shop_unread' : 'user_unread';
        $this->forceFill([
            'last_message_at' => $message->created_at,
            $counter => $this->{$counter} + 1,
        ])->save();

        return $message;
    }

    /**
     * Mark everything the given side has not read yet as read.
     */
    public function markReadBy(string $side): void
    {
        $otherSide = $side === 'user' ? 'shop' : 'user';

        $this->messages()
            ->where('sender_type', $otherSide)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->forceFill([$side . '_unread' => 0])->save();
    }
}
