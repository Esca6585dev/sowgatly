<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComposition extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'composition_id', 'qty', 'qty_type'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function composition()
    {
        return $this->belongsTo(Composition::class);
    }
}