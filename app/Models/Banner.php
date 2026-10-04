<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Promo banner shown on the app home screen (and later on the website).
 */
class Banner extends Model
{
    use HasFactory;

    public const LINK_TYPES = ['none', 'category', 'product', 'shop', 'url'];

    protected $fillable = [
        'title_tm', 'title_ru', 'title_en',
        'subtitle_tm', 'subtitle_ru', 'subtitle_en',
        'image', 'link_type', 'link_value', 'region_id',
        'position', 'is_active', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /** Columns the admin panel's quick search looks through. */
    public static function fillableData(): array
    {
        return ['title_tm', 'title_ru', 'title_en'];
    }

    /** Active banners inside their date window, global or for the given city. */
    public function scopeVisible(Builder $query, ?int $regionId = null): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->when($regionId !== null,
                fn ($q) => $q->where(fn ($r) => $r->whereNull('region_id')->orWhere('region_id', $regionId)),
                fn ($q) => $q->whereNull('region_id'))
            ->orderBy('position')
            ->orderByDesc('id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    protected static function booted(): void
    {
        // The home feed caches banners; any change invalidates it.
        static::saved(fn () => HomeFeed::bump());
        static::deleted(fn () => HomeFeed::bump());
    }
}
