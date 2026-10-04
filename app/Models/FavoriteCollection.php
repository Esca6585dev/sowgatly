<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteCollection extends Model
{
    use HasFactory;

    public const MAX_PER_USER = 50;
    public const MAX_ITEMS = 500;

    protected $fillable = ['user_id', 'name', 'position'];

    protected $casts = ['position' => 'integer'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(FavoriteCollectionItem::class, 'collection_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'favorite_collection_items', 'collection_id', 'product_id')
            ->withTimestamps();
    }
}
