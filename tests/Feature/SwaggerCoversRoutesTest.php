<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Every /api route must be documented. Regenerate the spec with
 * `php artisan l5-swagger:generate` after adding or renaming a route.
 */
class SwaggerCoversRoutesTest extends TestCase
{
    public function test_every_api_route_is_in_the_swagger_spec(): void
    {
        $specFile = storage_path('api-docs/api-docs.json');
        $this->assertFileExists($specFile, 'Run php artisan l5-swagger:generate');

        $spec = json_decode(file_get_contents($specFile), true);
        $documented = collect($spec['paths'] ?? [])->keys()->map(fn ($p) => $this->normalize($p));

        $routes = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => $route->uri())
            ->filter(fn ($uri) => str_starts_with($uri, 'api/'))
            ->reject(fn ($uri) => str_starts_with($uri, 'api/documentation') || str_starts_with($uri, 'api/oauth2'))
            ->map(fn ($uri) => $this->normalize('/' . $uri))
            ->unique()
            ->values();

        $missing = $routes->diff($documented)->values()->all();

        $this->assertSame([], $missing, 'Routes missing from Swagger: ' . implode(', ', $missing));
    }

    /** `{id}` and `{product}` are the same path for this purpose. */
    private function normalize(string $path): string
    {
        return preg_replace('/\{[^}]+\}/', '{}', $path);
    }
}
