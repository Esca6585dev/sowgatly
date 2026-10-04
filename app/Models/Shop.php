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