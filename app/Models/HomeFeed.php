<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

/**
 * Cache versioning for GET /api/home. The feed is cached per region and
 * locale under a key that includes a version number; bumping the version
 * invalidates every cached variant at once (the file cache has no tags).
 */
final class HomeFeed
{
    public const TTL_SECONDS = 300;

    public static function version(): int
    {
        return (int) Cache::get('home_feed:version', 1);
    }

    public static function bump(): void
    {
        Cache::forever('home_feed:version', self::version() + 1);
    }

    public static function key(?int $regionId, string $locale, int $perSection): string
    {
        return sprintf('home_feed:v%d:r%s:%s:%d', self::version(), $regionId ?? 'all', $locale, $perSection);
    }
}
