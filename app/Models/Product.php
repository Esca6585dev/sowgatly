<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $table = 'products';

    protected $fillable = [
        'name_tm',
        'name_en',
        'name_ru',
        'description_tm',
        'description_en',
        'description_ru',
        'price',
        'discount',
        'stock',
        'production_time',
        'min_order',
        'seller_status',
        'status',
        'shop_id',
        'category_id',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'integer',
        'stock' => 'integer',
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

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Products customers may see: approved by the admin and switched on by the shop. */
    public function scopeStorefront($query)
    {
        return $query->where('status', true)->where('seller_status', true);
    }

    /** Adds `reviews_avg` and `reviews_count` columns for ProductResource. */
    public function scopeWithRatingSummary($query)
    {
        return $query->withAvg('reviews as reviews_avg', 'rating')->withCount('reviews');
    }

    /** Can be made within three hours, so it can be delivered today. */
    public function scopeDeliveryToday($query)
    {
        return $query->whereNotNull('production_time')->where('production_time', '<=', 180);
    }

    /** Most ordered in the last 30 days first (`popularity` column), newest as tiebreaker. */
    public function scopePopular($query)
    {
        return $query->withCount(['orderItems as popularity' => function ($q) {
            $q->where('order_items.created_at', '>=', now()->subDays(30));
        }])->orderByDesc('popularity')->latest('products.created_at');
    }

    /** Average rating, from the eager-loaded summary when present. */
    public function ratingAverage(): ?float
    {
        $avg = array_key_exists('reviews_avg', $this->attributes)
            ? $this->attributes['reviews_avg']
            : $this->reviews()->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function ratingCount(): int
    {
        return (int) (array_key_exists('reviews_count', $this->attributes)
            ? $this->attributes['reviews_count']
            : $this->reviews()->count());
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