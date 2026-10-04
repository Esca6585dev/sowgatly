<?php

namespace Tests\Feature\Api;

use App\Models\Shop;
use App\Models\User;
use App\Models\UserNotification;
use Laravel\Sanctum\Sanctum;

class ChatTest extends ApiTestCase
{
    /** @test */
    public function customer_and_shop_exchange_messages_with_unread_counters()
    {
        $owner = User::factory()->create();
        $shop = Shop::factory()->create(['user_id' => $owner->id]);

        // Opening a thread is idempotent.
        $thread = $this->postJson('/api/me/chats', ['shop_id' => $shop->id])->assertStatus(201)->json('data');
        $this->postJson('/api/me/chats', ['shop_id' => $shop->id])->assertStatus(200)->assertJsonPath('data.id', $thread['id']);

        $this->postJson("/api/me/chats/{$thread['id']}/messages", ['body' => 'Salam, <b>gül</b> barmy?'])
            ->assertStatus(201)
            ->assertJsonPath('data.body', 'Salam, gül barmy?');

        $this->assertEquals(1, UserNotification::where('user_id', $owner->id)->where('type', 'chat_message')->count());

        // Shop side sees one unread, replies, and the customer gets one unread.
        Sanctum::actingAs($owner);
        $this->getJson('/api/shop/chats/unread-count')->assertOk()->assertJsonPath('unread', 1);
        $this->getJson('/api/shop/chats')->assertOk()->assertJsonPath('data.0.unread', 1)->assertJsonPath('data.0.user.id', $this->user->id);
        $this->postJson("/api/shop/chats/{$thread['id']}/read")->assertOk();
        $this->getJson('/api/shop/chats/unread-count')->assertOk()->assertJsonPath('unread', 0);
        $this->postJson("/api/shop/chats/{$thread['id']}/messages", ['body' => 'Hawa, bar'])->assertStatus(201);

        Sanctum::actingAs($this->user);
        $this->getJson('/api/me/chats/unread-count')->assertOk()->assertJsonPath('unread', 1);
        $messages = $this->getJson("/api/me/chats/{$thread['id']}/messages")->assertOk()->json('data');
        $this->assertCount(2, $messages);
        $this->assertEquals('user', $messages[0]['sender_type']);
        $this->assertEquals('shop', $messages[1]['sender_type']);

        // Cursor pagination returns only newer messages.
        $this->getJson("/api/me/chats/{$thread['id']}/messages?after={$messages[0]['id']}")->assertOk()->assertJsonCount(1, 'data');

        $this->postJson("/api/me/chats/{$thread['id']}/read")->assertOk();
        $this->getJson('/api/me/chats/unread-count')->assertOk()->assertJsonPath('unread', 0);
    }

    /** @test */
    public function a_thread_is_private_to_its_customer_and_shop()
    {
        $shop = Shop::factory()->create();
        $thread = $this->postJson('/api/me/chats', ['shop_id' => $shop->id])->json('data');

        Sanctum::actingAs(User::factory()->create());
        $this->getJson("/api/me/chats/{$thread['id']}/messages")->assertStatus(404);
        $this->postJson("/api/me/chats/{$thread['id']}/messages", ['body' => 'hi'])->assertStatus(404);
        $this->getJson('/api/shop/chats')->assertStatus(403);
    }

    /** @test */
    public function thread_bound_to_an_order_must_belong_to_the_customer()
    {
        $shop = Shop::factory()->create();
        $this->postJson('/api/me/chats', ['shop_id' => $shop->id, 'order_id' => 999])->assertStatus(404);
    }
}
