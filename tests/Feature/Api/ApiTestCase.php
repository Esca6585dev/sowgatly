<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\Region;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Shared fixtures for API feature tests.
 *
 * `$this->user` is a plain customer. Call `actingAsCustomer()` to send requests
 * with a Sanctum token for them.
 */
abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    protected function actingAsCustomer(?User $user = null): static
    {
        Sanctum::actingAs($user ?? $this->user, ['*']);

        return $this;
    }

    /** A city region (with its province and country parents). */
    protected function cityRegion(string $name = 'Aşgabat'): Region
    {
        return Region::factory()->city()->create(['name' => $name]);
    }

    /** A shop in the given city with `$count` active products. */
    protected function shopWithProducts(int $count = 3, ?Region $city = null, ?Category $category = null): Shop
    {
        $shop = Shop::factory()->create(['region_id' => ($city ?? $this->cityRegion())->id]);

        Product::factory()
            ->count($count)
            ->withImages(1)
            ->create([
                'shop_id' => $shop->id,
                'category_id' => ($category ?? Category::factory()->create())->id,
            ]);

        return $shop;
    }

    /** @return Collection<int, Product> */
    protected function productsOf(Shop $shop): Collection
    {
        return $shop->products()->get();
    }
}
