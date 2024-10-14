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
        'user_id',
        'region_id',
    ];

    protected $casts = [
        'email' => 'string',
        'mon_fri_open' => 'string',
        'mon_fri_close' => 'string',
        'sat_sun_open' => 'string',
        'sat_sun_close' => 'string',
        'image' => 'string',
    ];

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
}