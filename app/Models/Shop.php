<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'mon_fri_open',
        'mon_fri_close',
        'sat_sun_open',
        'sat_sun_close',
        'image',
        'phone',
        'user_id',
        'region_id',
        'delivery_fee',
        'pickup_available',
        'min_order_amount',
        'description_tm',
        'description_ru',
        'description_en',
        'status',
    ];

    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $casts = [
        'email' => 'string',
        'mon_fri_open' => 'string',
        'mon_fri_close' => 'string',
        'sat_sun_open' => 'string',
        'sat_sun_close' => 'string',
        'image' => 'string',
        'delivery_fee' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'pickup_available' => 'boolean',
    ];

    /**
     * Columns the admin panel's quick search looks through.
     */
    public static function fillableData(): array
    {
        return ['name', 'email', 'phone'];
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Adds `rating_avg` and `reviews_count` (over all the shop's product
     * reviews) as subselects, so lists stay at one query.
     */
    public function scopeWithRatingSummary($query)
    {
        $reviews = ProductReview::query()
            ->join('products', 'products.id', '=', 'product_reviews.product_id')
            ->whereColumn('products.shop_id', 'shops.id');

        return $query
            ->addSelect(['shops.*'])
            ->addSelect(['rating_avg' => (clone $reviews)->selectRaw('AVG(product_reviews.rating)')])
            ->addSelect(['reviews_count' => (clone $reviews)->selectRaw('COUNT(*)')]);
    }

    public function ratingAverage(): ?float
    {
        $avg = array_key_exists('rating_avg', $this->attributes)
            ? $this->attributes['rating_avg']
            : ProductReview::whereIn('product_id', $this->products()->select('id'))->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function ratingCount(): int
    {
        return (int) (array_key_exists('reviews_count', $this->attributes)
            ? $this->attributes['reviews_count']
            : ProductReview::whereIn('product_id', $this->products()->select('id'))->count());
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function chatThreads()
    {
        return $this->hasMany(ChatThread::class);
    }
}