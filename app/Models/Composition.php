<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Composition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the products that use this composition.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_compositions')
                    ->withPivot('qty', 'qty_type');
    }

    /**
     * Get the product compositions for this composition.
     */
    public function productCompositions()
    {
        return $this->hasMany(ProductComposition::class);
    }
}