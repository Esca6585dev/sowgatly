<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = ['thread_id', 'sender_type', 'sender_id', 'body', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function thread()
    {
        return $this->belongsTo(ChatThread::class, 'thread_id');
    }
}
