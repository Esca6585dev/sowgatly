<?php

namespace Tests\Feature\Api;

use App\Models\ShopApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShopApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_guest_can_apply_to_open_a_shop()
    {
        $this->postJson('/api/shop-applications', ['name' => 'Gül dünýäsi', 'phone' => '65656585'])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'new');

        $this->assertDatabaseHas('shop_applications', ['name' => 'Gül dünýäsi', 'user_id' => null]);
    }

    /** @test */
    public function a_signed_in_user_is_linked_to_the_application_and_can_list_them()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/shop-applications', ['name' => 'Roza', 'phone' => '65656585', 'description' => 'Flowers'])->assertStatus(201);
        $this->assertEquals($user->id, ShopApplication::first()->user_id);

        $this->getJson('/api/me/shop-applications')->assertOk()->assertJsonCount(1, 'data');
    }

    /** @test */
    public function phone_is_validated()
    {
        $this->postJson('/api/shop-applications', ['name' => 'X', 'phone' => '123'])->assertStatus(422);
    }
}
