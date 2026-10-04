<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id', 'order_id', 'rating', 'comment',
        'rating_match', 'rating_value', 'rating_service',
    ];

    public const CRITERIA = ['rating_match', 'rating_value', 'rating_service'];

    protected $casts = [
        'rating' => 'integer',
        'rating_match' => 'integer',
        'rating_value' => 'integer',
        'rating_service' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
