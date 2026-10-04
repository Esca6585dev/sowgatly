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
}
