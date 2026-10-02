<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'parent_id'];

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }

    public function parent()
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    /**
     * IDs of this region and everything under it (province -> city ->
     * village), so filtering by a province also matches shops placed in
     * one of its cities or villages.
     */
    public static function selfAndDescendantIds($regionId): array
    {
        $ids = [(int) $regionId];
        $frontier = [(int) $regionId];

        while (!empty($frontier)) {
            $frontier = static::whereIn('parent_id', $frontier)->pluck('id')->all();
            $ids = array_merge($ids, $frontier);
        }

        return $ids;
    }
}