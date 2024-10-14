<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'logo', 'status'];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'logo' => 'string',
        'status' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'brands_products', 'brands_id', 'products_id');
    }
}