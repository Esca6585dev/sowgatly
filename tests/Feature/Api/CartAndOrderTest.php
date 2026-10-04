<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;

class CartAndOrderTest extends ApiTestCase
{
    public function test_cart_lifecycle(): void
    {
        $shop = $this->shopWithProducts(2);
        [$first, $second] = $this->productsOf($shop)->all();
        $first->update(['price' => 100, 'discount' => 10, 'stock' => 10]);
        $second->update(['price' => 50, 'discount' => null, 'stock' => 10]);

        $this->actingAsCustomer();

        $this->getJson('/api/cart')->assertOk()->assertJson(['message' => 'Cart is empty']);

        $this->postJson('/api/cart/add', ['product_id' => $first->id, 'quantity' => 2])
            ->assertOk()
            ->assertJson(['success' => true]);
        $this->postJson('/api/cart/add', ['product_id' => $second->id, 'quantity' => 1])->assertOk();

        $cart = $this->getJson('/api/cart')->assertOk()->assertJsonCount(2, 'cart.items')->json();
        // 2 × 90 (10 % off) + 1 × 50
        $this->assertEquals(230, $cart['total_amount']);

        $itemId = $cart['cart']['items'][1]['id'];
        $this->putJson("/api/cart/items/{$itemId}", ['quantity' => 3])->assertOk();
        $this->assertEquals(330, $this->getJson('/api/cart')->json('total_amount'));

        $this->deleteJson("/api/cart/items/{$itemId}")->assertOk();
        $this->getJson('/api/cart')->assertJsonCount(1, 'cart.items');
    }

    public function test_adding_more_than_stock_is_rejected(): void
    {
        $product = $this->productsOf($this->shopWithProducts(1))->first();
        $product->update(['stock' => 1]);

        $this->actingAsCustomer()
            ->postJson('/api/cart/add', ['product_id' => $product->id, 'quantity' => 5])
            ->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Not enough stock']);
    }

    public function test_cart_items_belong_to_the_caller(): void
    {
        $product = $this->productsOf($this->shopWithProducts(1))->first();
        $this->actingAsCustomer()->postJson('/api/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $itemId = $this->getJson('/api/cart')->json('cart.items.0.id');

        $this->actingAsCustomer(User::factory()->create())
            ->putJson("/api/cart/items/{$itemId}", ['quantity' => 2])
            ->assertStatus(404);
    }

    public function test_checkout_creates_an_order_with_discounted_prices_and_clears_the_cart(): void
    {
        // Today checkout is single-shop: every cart item lands in one order
        // under the first item's shop. Per-shop splitting is a later phase.
        $shop = $this->shopWithProducts(2);
        [$a, $b] = $this->productsOf($shop)->all();
        $a->update(['price' => 100, 'discount' => 20, 'stock' => 5]);
        $b->update(['price' => 40, 'discount' => null, 'stock' => 5]);

        $this->actingAsCustomer();
        $this->postJson('/api/cart/add', ['product_id' => $a->id, 'quantity' => 2]);
        $this->postJson('/api/cart/add', ['product_id' => $b->id, 'quantity' => 1]);

        $this->postJson('/api/orders', [
            'delivery_type' => 'asap',
            'recipient_phone' => '65123456',
            'delivery_address' => 'Aşgabat, Magtymguly 10',
            'note' => 'Call before delivery',
        ])->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('order.shop_id', $shop->id)
            ->assertJsonCount(2, 'order.items');

        $order = Order::where('user_id', $this->user->id)->firstOrFail();
        // 2 × 80 (20 % off) + 1 × 40, plus the shop's default 20 TMT delivery fee
        $this->assertEquals(200, $order->items_total);
        $this->assertEquals(20, $order->delivery_fee);
        $this->assertEquals(220, $order->total_amount);
        $this->assertEquals(80, $order->items->where('product_id', $a->id)->first()->price);
        $this->assertSame('pending', $order->status);
        $this->assertSame(3, $a->fresh()->stock);

        $this->getJson('/api/cart')->assertJson(['message' => 'Cart is empty']);
    }

    public function test_scheduled_delivery_requires_a_future_time(): void
    {
        $product = $this->productsOf($this->shopWithProducts(1))->first();
        $this->actingAsCustomer()->postJson('/api/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $this->postJson('/api/orders', [
            'delivery_type' => 'scheduled',
            'recipient_phone' => '65123456',
            'delivery_address' => 'x',
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->postJson('/api/orders', [
            'delivery_type' => 'scheduled',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'recipient_phone' => '65123456',
            'delivery_address' => 'x',
        ])->assertStatus(201)->assertJson(['success' => true]);
    }

    public function test_checkout_with_an_empty_cart_fails(): void
    {
        $this->actingAsCustomer()
            ->postJson('/api/orders', ['delivery_type' => 'asap', 'recipient_phone' => '65123456', 'delivery_address' => 'x'])
            ->assertStatus(400);
    }

    public function test_orders_are_listed_and_scoped_to_the_caller(): void
    {
        $mine = Order::factory()->create(['user_id' => $this->user->id]);
        $theirs = Order::factory()->create();

        $this->actingAsCustomer();

        $this->getJson('/api/orders')->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $mine->id);
        $this->getJson("/api/orders/{$mine->id}")->assertOk()->assertJsonPath('id', $mine->id);
        $this->getJson("/api/orders/{$theirs->id}")->assertStatus(404);
    }

    public function test_only_pending_orders_can_be_cancelled(): void
    {
        $pending = Order::factory()->create(['user_id' => $this->user->id]);
        $processing = Order::factory()->status('processing')->create(['user_id' => $this->user->id]);

        $this->actingAsCustomer();

        $this->postJson("/api/orders/{$pending->id}/cancel")->assertOk()->assertJsonPath('order.status', 'cancelled');
        $this->postJson("/api/orders/{$processing->id}/cancel")->assertStatus(422);
    }
}
