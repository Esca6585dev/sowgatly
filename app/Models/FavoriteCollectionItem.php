<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteCollectionItem extends Model
{
    protected $fillable = ['collection_id', 'product_id'];

    public function collection()
    {
        return $this->belongsTo(FavoriteCollection::class, 'collection_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
