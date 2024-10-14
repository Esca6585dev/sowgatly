<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'username',
        'email',
        'messages',
        'user_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class,'user_id', 'id');
    }

    protected function fillableData()
    {
        return $this->fillable;
    }
}
