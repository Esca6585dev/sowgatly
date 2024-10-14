<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $table = 'attributes';
    
    protected $fillable = [
        'type',
        'value',
        'category_id',
    ];

    protected function fillableData()
    {
        return $this->fillable;
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
