<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code_number',
        'bolum',
        'user_id',
        'ip_address',
        'address',
        'phone_number',
        'file',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function section()
    {
        return $this->hasOne(Section::class, 'id', 'bolum');
    }

    public function filterNumber($phone_number)
    {
        // Filter the Numbers from String
        return ltrim(preg_replace('/[^0-9]/', '', $phone_number), '993');
    }

    protected function fillableData()
    {
        return $this->fillable;
    }
}
