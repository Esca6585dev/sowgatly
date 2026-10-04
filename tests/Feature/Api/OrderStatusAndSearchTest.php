<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class OrderStatusAndSearchTest extends ApiTestCase
{
    private function makeOrder(User $customer, Shop $shop, string $productName, string $status = 'pending'): Order
    {
        $product = Product::factory()->create(['shop_id' => $shop->id, 'name_tm' => $productName, 'name_ru' => $productName, 'name_en' => $productName]);
        $order = Order::create([
            'user_id' => $customer->id,
            'shop_id' => $shop->id,
            'total_amount' => 120,
            'items_total' => 100,
            'delivery_fee' => 20,
            'status' => $status,
            'delivery_type' => 'asap',
            'recipient_phone' => '65656585',
            'delivery_address' => 'x',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 100]);

        return $order;
    }

    /** @test */
    public function customer_can_search_orders_by_number_and_product_name()
    {
        $shop = Shop::factory()->create();
        $peonies = $this->makeOrder($this->user, $shop, 'Monobuket pion');
        $roses = $this->makeOrder($this->user, $shop, 'Gyzyl bägül');

        $this->getJson('/api/orders?q=pion')->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $peonies->id);
        $this->getJson('/api/orders?q=' . $roses->id)->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $roses->id);
        // Ids are not reset between tests on MySQL, so build the padded number from the real id.
        $this->getJson('/api/orders?q=' . $roses->number)->assertOk()->assertJsonCount(1)->assertJsonPath('0.number', $roses->number);
        $this->getJson('/api/orders')->assertOk()->assertJsonCount(2);
    }

    /** @test */
    public function shop_moves_an_order_through_delivering_to_completed()
    {
        $owner = User::factory()->create();
        $shop = Shop::factory()->create(['user_id' => $owner->id]);
        $order = $this->makeOrder($this->user, $shop, 'Lale', 'processing');

        Sanctum::actingAs($owner);

        $this->putJson("/api/shop/orders/{$order->id}/status", ['status' => 'delivering'])
            ->assertOk()->assertJsonPath('data.status', 'delivering');

        // A delivering order can no longer be cancelled, only completed.
        $this->putJson("/api/shop/orders/{$order->id}/status", ['status' => 'cancelled'])->assertStatus(422);

        $this->putJson("/api/shop/orders/{$order->id}/status", ['status' => 'completed'])
            ->assertOk()->assertJsonPath('data.status', 'completed');
    }
}
