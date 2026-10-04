<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $table = 'products';

    protected $fillable = [
        // Columns that exist in the products table.
        'name_tm',
        'name_en',
        'name_ru',
        'description_tm',
        'description_en',
        'description_ru',
        'stock',
        // Legacy keys kept for the shop-owner product endpoints.
        'name', 
        'description',
        'price',
        'discount',
        'code', 
        'category_id',
        'shop_id',
        'status',
        'gender',
        'sizes',
        'separated_sizes',
        'color',
        'manufacturer',
        'width',
        'height',
        'weight',
        'production_time',
        'min_order',
        'seller_status'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'integer',
        'sizes' => 'array',
        'separated_sizes' => 'array',
        'width' => 'double',
        'height' => 'double',
        'weight' => 'double',
        'production_time' => 'integer',
        'min_order' => 'integer',
        'seller_status' => 'boolean',
        'status' => 'boolean',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brands_products', 'products_id', 'brands_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function compositions()
    {
        return $this->belongsToMany(Composition::class, 'compositions_products')
                    ->withPivot('qty', 'qty_type')
                    ->withTimestamps();
    }

    // Helper method
    public function getDiscountedPrice()
    {
        return $this->price - ($this->price * $this->discount / 100);
    }

    // Scope to filter products by brand
    public function scopeWithBrand($query, $brandId)
    {
        return $query->whereJsonContains('brand_ids', $brandId);
    }

    public function scopeWithFullDetails($query)
    {
        return $query->with(['category', 'shop', 'brands', 'images', 'compositions']);
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function getAttributeValues($key)
    {
        $attribute = $this->attributes()->where('attribute_key', $key)->first();
        return $attribute ? json_decode($attribute->attribute_value, true) : null; 
    }

    public function getSizes() 
    {
        return $this->getAttributeValues('size');
    }

    public function getColors() 
    {
        return $this->getAttributeValues('color');
    }
}