<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Standart extends Model
{
    use HasFactory, SoftDeletes;

    public $fillable = [
        'number',
        'name_tm',
        'name_en',
        'name_ru',
        'name_tr',
    ];

    public function cart()
    {
        return $this->hasOne(Cart::class,'standart_id', 'id')->where('user_id', auth()->user()->id);
    }

    protected function fillableData()
    {
        return $this->fillable;
    }
}
