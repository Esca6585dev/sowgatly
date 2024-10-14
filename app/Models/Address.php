<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'address_name',
        'postal_code',
    ];

    protected $casts = [
        'address_name' => 'string',
        'postal_code' => 'string',
    ];

    /**
     * Get the shop that owns the address.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the full address as a string.
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address_name,
            $this->postal_code
        ]);

        return implode(', ', $parts);
    }
}