<?php

namespace Tests\Feature\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Region;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

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

    protected function cityRegion(): Region
    {
        return Region::factory()->create(['type' => 'city']);
    }

    protected function shopWithProducts(int $count = 1, array $shopAttributes = []): Shop
    {
        $shop = Shop::factory()->create(array_merge(['region_id' => $this->cityRegion()->id], $shopAttributes));
        Product::factory()->count($count)->create(['shop_id' => $shop->id, 'price' => 100, 'discount' => 0]);

        return $shop;
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
