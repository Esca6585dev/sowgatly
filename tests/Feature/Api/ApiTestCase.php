<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\CartItem;
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
 * `$this->user` is a plain customer and every request is sent with their
 * Sanctum token by default. Use `actingAsCustomer($other)` to switch users;
 * tests that need an unauthenticated request extend `Tests\TestCase` instead.
 */
abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    protected function actingAsCustomer(?User $user = null): static
    {
        Sanctum::actingAs($user ?? $this->user, ['*']);

        return $this;
    }

    /** A city region with its province and country parents. */
    protected function cityRegion(?string $name = null): Region
    {
        return Region::factory()->city()->create($name ? ['name' => $name] : []);
    }

    /**
     * A shop (in a fresh city unless `region_id` is given) with `$count`
     * active products at 100 TMT and no discount.
     */
    protected function shopWithProducts(int $count = 1, array $shopAttributes = [], array $productAttributes = []): Shop
    {
        $shop = Shop::factory()->create(array_merge(['region_id' => $this->cityRegion()->id], $shopAttributes));

        Product::factory()
            ->count($count)
            ->create(array_merge(['shop_id' => $shop->id, 'price' => 100, 'discount' => 0], $productAttributes));

        return $shop;
    }

    /** @return Collection<int, Product> */
    protected function productsOf(Shop $shop): Collection
    {
        return $shop->products()->orderBy('id')->get();
    }

    protected function fillCart(User $user, Shop $shop, int $quantity = 2): Cart
    {
        $cart = Cart::create(['user_id' => $user->id]);
        foreach ($shop->products as $product) {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        return $cart;
    }

    protected function orderPayload(array $overrides = []): array
    {
        return array_merge([
            'delivery_type' => 'asap',
            'recipient_phone' => '65656585',
            'delivery_address' => 'Aýtakow köç. 17',
        ], $overrides);
    }
}
