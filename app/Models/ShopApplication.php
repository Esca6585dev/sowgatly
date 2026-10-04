<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopApplication extends Model
{
    use HasFactory;

    public const STATUSES = ['new', 'contacted', 'approved', 'rejected'];

    protected $fillable = ['user_id', 'name', 'phone', 'region_id', 'description', 'status', 'admin_note'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
