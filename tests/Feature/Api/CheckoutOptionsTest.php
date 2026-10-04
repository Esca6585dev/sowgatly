<?php

namespace Tests\Feature\Api;

use App\Models\Order;

class CheckoutOptionsTest extends ApiTestCase
{
    /** @test */
    public function old_payload_without_new_fields_still_creates_a_delivery_order_with_the_shop_fee()
    {
        $shop = $this->shopWithProducts(1, ['delivery_fee' => 20]);
        $this->fillCart($this->user, $shop, 2); // 2 x 100

        $response = $this->postJson('/api/orders', $this->orderPayload());

        $response->assertStatus(201)
            ->assertJsonPath('order.fulfillment', 'delivery')
            ->assertJsonPath('order.payment_method', 'cash')
            ->assertJsonPath('order.payment_status', 'unpaid')
            ->assertJsonPath('order.number', '0000001');

        $order = Order::first();
        $this->assertEquals(200, (float) $order->items_total);
        $this->assertEquals(20, (float) $order->delivery_fee);
        $this->assertEquals(220, (float) $order->total_amount);
    }

    /** @test */
    public function pickup_order_needs_no_address_and_has_no_fee()
    {
        $shop = $this->shopWithProducts(1, ['delivery_fee' => 20, 'pickup_available' => true]);
        $this->fillCart($this->user, $shop, 1);

        $response = $this->postJson('/api/orders', $this->orderPayload([
            'fulfillment' => 'pickup',
            'delivery_address' => null,
        ]));

        $response->assertStatus(201)->assertJsonPath('order.fulfillment', 'pickup');
        $order = Order::first();
        $this->assertEquals(0, (float) $order->delivery_fee);
        $this->assertEquals(100, (float) $order->total_amount);
        $this->assertNull($order->delivery_address);
    }

    /** @test */
    public function pickup_is_rejected_when_the_shop_does_not_offer_it()
    {
        $shop = $this->shopWithProducts(1, ['pickup_available' => false]);
        $this->fillCart($this->user, $shop, 1);

        $this->postJson('/api/orders', $this->orderPayload(['fulfillment' => 'pickup']))
            ->assertStatus(422);
    }

    /** @test */
    public function delivery_order_requires_an_address()
    {
        $shop = $this->shopWithProducts();
        $this->fillCart($this->user, $shop);

        $this->postJson('/api/orders', $this->orderPayload(['delivery_address' => null]))
            ->assertStatus(422);
    }

    /** @test */
    public function online_payment_requires_a_known_bank()
    {
        $shop = $this->shopWithProducts();
        $this->fillCart($this->user, $shop);

        $this->postJson('/api/orders', $this->orderPayload(['payment_method' => 'online']))
            ->assertStatus(422);

        $this->postJson('/api/orders', $this->orderPayload(['payment_method' => 'online', 'payment_bank' => 'unknown']))
            ->assertStatus(422);

        $this->postJson('/api/orders', $this->orderPayload(['payment_method' => 'online', 'payment_bank' => 'rysgal', 'recipient_name' => 'Aýna']))
            ->assertStatus(201)
            ->assertJsonPath('order.payment_method', 'online')
            ->assertJsonPath('order.payment_bank', 'rysgal')
            ->assertJsonPath('order.payment_status', 'unpaid')
            ->assertJsonPath('order.recipient_name', 'Aýna');
    }

    /** @test */
    public function payment_methods_are_public()
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/payment-methods')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['code' => 'rysgal'])
            ->assertJsonFragment(['code' => 'cash']);
    }
}
