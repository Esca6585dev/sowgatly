<?php

namespace Tests\Feature\Api;

use App\Models\UserAddress;
use App\Models\UserNotification;
use App\Models\User;

class CustomerAccountTest extends ApiTestCase
{
    public function test_me_and_update_me(): void
    {
        $this->actingAsCustomer();

        $this->getJson('/api/users/me')->assertOk()->assertJsonPath('data.phone_number', $this->user->phone_number);

        $this->putJson('/api/users/me', ['name' => 'Aýna', 'email' => 'ayna@example.com'])
            ->assertOk()
            ->assertJsonPath('user.name', 'Aýna');

        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'name' => 'Aýna', 'email' => 'ayna@example.com']);
    }

    public function test_favorites_toggle_and_list(): void
    {
        $product = $this->productsOf($this->shopWithProducts(1))->first();
        $this->actingAsCustomer();

        $this->postJson('/api/favorites/toggle', ['product_id' => $product->id])->assertOk()->assertJson(['favorited' => true]);
        $this->getJson('/api/favorites')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $product->id);

        $this->postJson('/api/favorites/toggle', ['product_id' => $product->id])->assertOk()->assertJson(['favorited' => false]);
        $this->getJson('/api/favorites')->assertJsonCount(0, 'data');
    }

    public function test_addresses_crud_and_default_handling(): void
    {
        $this->actingAsCustomer();

        $first = $this->postJson('/api/me/addresses', ['title' => 'Home', 'address' => 'Aşgabat, Parahat 7'])
            ->assertStatus(201)
            ->assertJsonPath('data.is_default', true)
            ->json('data.id');

        $second = $this->postJson('/api/me/addresses', ['address' => 'Aşgabat, Mir 2', 'is_default' => true])
            ->assertStatus(201)
            ->json('data.id');

        $this->assertFalse((bool) UserAddress::find($first)->is_default);
        $this->assertTrue((bool) UserAddress::find($second)->is_default);

        $this->putJson("/api/me/addresses/{$first}", ['address' => 'Aşgabat, Parahat 8'])->assertOk();
        $this->getJson('/api/me/addresses')->assertOk()->assertJsonCount(2, 'data');
        $this->deleteJson("/api/me/addresses/{$second}")->assertOk();
        $this->assertDatabaseCount('user_addresses', 1);
    }

    public function test_addresses_of_other_users_are_invisible(): void
    {
        $foreign = UserAddress::factory()->create();

        $this->actingAsCustomer()
            ->putJson("/api/me/addresses/{$foreign->id}", ['address' => 'hack'])
            ->assertStatus(404);
        $this->deleteJson("/api/me/addresses/{$foreign->id}")->assertStatus(404);
    }

    public function test_notifications_list_and_mark_all_read(): void
    {
        UserNotification::create(['user_id' => $this->user->id, 'type' => 'order_status', 'data' => ['order_id' => 1, 'status' => 'processing']]);
        UserNotification::create(['user_id' => User::factory()->create()->id, 'type' => 'order_status', 'data' => []]);

        $this->actingAsCustomer();

        $this->getJson('/api/me/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.unread', 1);

        $this->postJson('/api/me/notifications/read')->assertOk();
        $this->getJson('/api/me/notifications')->assertJsonPath('meta.unread', 0);
    }
}
