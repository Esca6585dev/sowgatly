<?php

namespace Tests\Feature\Api;

use App\Models\Shop;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class ShopWriteTest extends ApiTestCase
{
    private function validShop(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Gül Dükany',
            'email' => 'shop@example.com',
            'mon_fri_open' => '09:00',
            'mon_fri_close' => '18:00',
            'sat_sun_open' => '10:00',
            'sat_sun_close' => '14:00',
        ], $overrides);
    }

    /** @test */
    public function creating_a_shop_requires_the_core_fields()
    {
        $this->postJson('/api/shops', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'mon_fri_open', 'mon_fri_close', 'sat_sun_open', 'sat_sun_close']);

        $this->postJson('/api/shops', $this->validShop(['mon_fri_open' => 'not-a-time']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['mon_fri_open']);

        $this->assertDatabaseCount('shops', 0);
    }

    /** @test */
    public function creating_a_shop_returns_201_with_the_shop()
    {
        Storage::fake('public');
        $region = $this->cityRegion();

        $response = $this->postJson('/api/shops', $this->validShop([
            'image' => UploadedFile::fake()->image('shop.jpg'),
            'region_id' => $region->id,
        ]));

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'Gül Dükany')
            ->assertJsonPath('data.region.id', $region->id);

        $shop = Shop::where('user_id', $this->user->id)->firstOrFail();
        Storage::disk('public')->assertExists($shop->image);
    }

    /** @test */
    public function updating_may_send_a_subset_of_fields()
    {
        $shop = Shop::factory()->create(['user_id' => $this->user->id]);

        $this->putJson("/api/shops/{$shop->id}", ['name' => 'Täze at'])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Täze at');

        $this->assertDatabaseHas('shops', ['id' => $shop->id, 'name' => 'Täze at', 'mon_fri_open' => $shop->mon_fri_open]);
    }

    /** @test */
    public function another_users_shop_cannot_be_updated()
    {
        $shop = Shop::factory()->create();

        $this->putJson("/api/shops/{$shop->id}", ['name' => 'Hijacked'])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'You do not have permission to update this shop']);
    }

    /** @test */
    public function login_lists_the_callers_shop()
    {
        $owner = User::factory()->create(['phone_number' => '65123456']);
        $shop = Shop::factory()->create(['user_id' => $owner->id]);
        UserOtp::create(['user_id' => $owner->id, 'otp' => '0000', 'expire_at' => now()->addMinutes(10)]);
        Sanctum::actingAs(User::factory()->create()); // irrelevant: login is a guest route

        $this->postJson('/api/login', ['phone_number' => '65123456', 'otp' => '0000'])
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(1, 'shops')
            ->assertJsonPath('shops.0.id', $shop->id);
    }
}
