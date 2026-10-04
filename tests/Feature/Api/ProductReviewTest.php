<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;

class ProductReviewTest extends ApiTestCase
{
    public function test_only_buyers_can_review(): void
    {
        $product = $this->productsOf($this->shopWithProducts(1))->first();

        $this->actingAsCustomer()
            ->postJson("/api/products/{$product->id}/reviews", ['rating' => 5, 'comment' => 'Great'])
            ->assertStatus(403);
    }

    public function test_a_buyer_can_review_once_and_the_list_shows_it(): void
    {
        $product = $this->productsOf($this->shopWithProducts(1))->first();
        $order = Order::factory()->status('completed')->create(['user_id' => $this->user->id, 'shop_id' => $product->shop_id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);

        $this->actingAsCustomer();

        $this->postJson("/api/products/{$product->id}/reviews", ['rating' => 4, 'comment' => 'Nice bouquet'])
            ->assertStatus(201)
            ->assertJsonPath('data.rating', 4);

        // A second review updates the first one instead of duplicating it.
        $this->postJson("/api/products/{$product->id}/reviews", ['rating' => 5])->assertStatus(201);
        $this->assertDatabaseCount('product_reviews', 1);

        $this->getJson("/api/products/{$product->id}/reviews")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.rating', 5)
            ->assertJsonPath('data.0.author', $this->user->name);
    }

    public function test_rating_is_validated(): void
    {
        $product = $this->productsOf($this->shopWithProducts(1))->first();

        $this->actingAsCustomer()
            ->postJson("/api/products/{$product->id}/reviews", ['rating' => 9])
            ->assertStatus(422);
    }

    public function test_three_criteria_derive_the_rating_and_link_the_order(): void
    {
        $product = $this->productsOf($this->shopWithProducts(1))->first();
        $order = Order::factory()->status('completed')->create(['user_id' => $this->user->id, 'shop_id' => $product->shop_id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);

        $this->actingAsCustomer();

        $this->getJson("/api/orders/{$order->id}")->assertOk()->assertJsonPath('items.0.reviewed', false);

        $this->postJson("/api/products/{$product->id}/reviews", [
            'rating_match' => 5,
            'rating_value' => 4,
            'rating_service' => 4,
            'comment' => 'Gowy',
            'order_id' => $order->id,
        ])->assertStatus(201)
            ->assertJsonPath('data.rating', 4)       // round((5+4+4)/3)
            ->assertJsonPath('data.rating_match', 5)
            ->assertJsonPath('data.order_id', $order->id);

        $this->getJson("/api/orders/{$order->id}")->assertOk()->assertJsonPath('items.0.reviewed', true);

        $this->getJson("/api/products/{$product->id}/reviews")
            ->assertOk()
            ->assertJsonPath('data.0.rating_service', 4)
            ->assertJsonPath('data.0.user.name', $this->user->name)
            ->assertJsonPath('meta.average', 4);

        // Paginated form only when `page` is sent.
        $this->getJson("/api/products/{$product->id}/reviews?page=1")
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.last_page', 1);
    }

    public function test_criteria_must_come_together_and_the_order_must_be_the_callers(): void
    {
        $product = $this->productsOf($this->shopWithProducts(1))->first();
        $mine = Order::factory()->status('completed')->create(['user_id' => $this->user->id, 'shop_id' => $product->shop_id]);
        OrderItem::factory()->create(['order_id' => $mine->id, 'product_id' => $product->id]);
        $foreign = Order::factory()->status('completed')->create(['shop_id' => $product->shop_id]);
        OrderItem::factory()->create(['order_id' => $foreign->id, 'product_id' => $product->id]);

        $this->actingAsCustomer();

        $this->postJson("/api/products/{$product->id}/reviews", ['rating_match' => 5])->assertStatus(422);
        $this->postJson("/api/products/{$product->id}/reviews", [])->assertStatus(422);
        $this->postJson("/api/products/{$product->id}/reviews", ['rating' => 5, 'order_id' => $foreign->id])->assertStatus(403);
        $this->postJson("/api/products/{$product->id}/reviews", ['rating' => 5, 'order_id' => $mine->id])->assertStatus(201);
    }
}
