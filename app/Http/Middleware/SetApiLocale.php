<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * API responses are localized by the Accept-Language header (tm, ru, en).
 * Anything else, or no header, keeps the default locale (Turkmen).
 */
class SetApiLocale
{
    public const SUPPORTED = ['tm', 'ru', 'en'];

    public function handle(Request $request, Closure $next)
    {
        // Not app.locale: Application::setLocale() rewrites that config key.
        $locale = config('app.fallback_locale', 'tm');

        foreach (explode(',', (string) $request->header('Accept-Language', '')) as $tag) {
            $code = strtolower(substr(trim(explode(';', $tag)[0]), 0, 2));
            if (in_array($code, self::SUPPORTED, true)) {
                $locale = $code;
                break;
            }
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
