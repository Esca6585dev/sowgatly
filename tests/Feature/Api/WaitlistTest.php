<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\UserNotification;

class WaitlistTest extends ApiTestCase
{
    /** @test */
    public function user_can_add_list_and_remove_waitlist_products()
    {
        $product = Product::factory()->create(['stock' => 0]);

        $this->postJson('/api/me/waitlist', ['product_id' => $product->id])->assertStatus(201);
        // Adding twice is idempotent.
        $this->postJson('/api/me/waitlist', ['product_id' => $product->id])->assertStatus(200);

        $this->getJson('/api/me/waitlist')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.product.id', $product->id);

        $this->deleteJson("/api/me/waitlist/{$product->id}")->assertOk();
        $this->getJson('/api/me/waitlist')->assertOk()->assertJsonCount(0, 'data');
        $this->deleteJson("/api/me/waitlist/{$product->id}")->assertStatus(404);
    }

    /** @test */
    public function restocking_a_product_notifies_waiting_users_once()
    {
        $product = Product::factory()->create(['stock' => 0, 'status' => true]);
        $this->postJson('/api/me/waitlist', ['product_id' => $product->id])->assertStatus(201);

        $product->update(['stock' => 5]);

        $this->assertEquals(1, UserNotification::where('user_id', $this->user->id)->where('type', 'product_available')->count());
        $this->assertNotNull($this->user->waitlistItems()->first()->notified_at);

        // Restocking again does not notify a second time.
        $product->update(['stock' => 0]);
        $product->update(['stock' => 3]);
        $this->assertEquals(1, UserNotification::where('user_id', $this->user->id)->where('type', 'product_available')->count());
    }
}
