<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Region;
use App\Models\Address;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ShopControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'phone_number' => $this->faker->numerify('########'), // 8-digit phone number
        ]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_list_shops()
    {
        Shop::factory()->count(5)->create();

        $response = $this->getJson('/api/shops');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'phone_number', 'image', 'mon_fri_open', 'mon_fri_close', 'sat_sun_open', 'sat_sun_close']
                     ],
                     'links',
                     'meta'
                 ]);
    }

    /** @test */
    public function it_can_create_a_shop()
    {
        Storage::fake('public');

        $shopData = [
            'name' => $this->faker->company,
            'email' => $this->faker->email,
            'phone_number' => $this->faker->numerify('########'), // 8-digit phone number
            'image' => UploadedFile::fake()->image('shop.jpg'),
            'mon_fri_open' => '09:00',
            'mon_fri_close' => '18:00',
            'sat_sun_open' => '10:00',
            'sat_sun_close' => '14:00',
            'street' => $this->faker->streetAddress,
            'settlement' => $this->faker->city,
            'district' => $this->faker->city,
            'province' => $this->faker->state,
            'region' => $this->faker->state,
            'country' => $this->faker->country,
            'postal_code' => $this->faker->postcode,
        ];

        $response = $this->postJson('/api/shops', $shopData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'shop' => [
                         'id', 'name', 'email', 'phone_number', 'image', 'mon_fri_open', 'mon_fri_close', 'sat_sun_open', 'sat_sun_close'
                     ]
                 ]);

        $this->assertDatabaseHas('shops', [
            'name' => $shopData['name'],
            'email' => $shopData['email'],
            'phone_number' => $shopData['phone_number'],
        ]);

        Storage::disk('public')->assertExists($response->json('shop.image'));
    }

    /** @test */
    public function it_can_show_a_shop()
    {
        $shop = Shop::factory()->create();

        $response = $this->getJson("/api/shops/{$shop->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['id', 'name', 'email', 'phone_number', 'image', 'mon_fri_open', 'mon_fri_close', 'sat_sun_open', 'sat_sun_close']
                 ]);
    }

    /** @test */
    public function it_can_update_a_shop()
    {
        $shop = Shop::factory()->create(['user_id' => $this->user->id]);

        $updatedData = [
            'name' => 'Updated Shop Name',
            'email' => 'updated@example.com',
            'phone_number' => '12345678', // 8-digit phone number
            'mon_fri_open' => '08:00',
            'mon_fri_close' => '17:00',
            'sat_sun_open' => '09:00',
            'sat_sun_close' => '13:00',
            'street' => 'Updated Street',
            'settlement' => 'Updated Settlement',
            'district' => 'Updated District',
            'province' => 'Updated Province',
            'region' => 'Updated Region',
            'country' => 'Updated Country',
            'postal_code' => '12345',
        ];

        $response = $this->putJson("/api/shops/{$shop->id}", $updatedData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['id', 'name', 'email', 'phone_number', 'image', 'mon_fri_open', 'mon_fri_close', 'sat_sun_open', 'sat_sun_close']
                 ]);

        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'name' => 'Updated Shop Name',
            'email' => 'updated@example.com',
            'phone_number' => '12345678',
        ]);
    }

    /** @test */
    public function it_cannot_update_shop_of_another_user()
    {
        $anotherUser = User::factory()->create();
        $shop = Shop::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->putJson("/api/shops/{$shop->id}", [
            'name' => 'Updated Shop Name',
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'message' => 'You do not have permission to update this shop',
                 ]);
    }

    /** @test */
    public function it_validates_required_fields_for_create()
    {
        $response = $this->postJson('/api/shops', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email', 'phone_number', 'mon_fri_open', 'mon_fri_close', 'sat_sun_open', 'sat_sun_close', 'street', 'settlement', 'district', 'province', 'region', 'country', 'postal_code']);
    }

    /** @test */
    public function it_validates_phone_number_format()
    {
        $response = $this->postJson('/api/shops', [
            'name' => 'Test Shop',
            'email' => 'test@example.com',
            'phone_number' => '1234567', // 7 digits, should be invalid
            'mon_fri_open' => '09:00',
            'mon_fri_close' => '18:00',
            'sat_sun_open' => '10:00',
            'sat_sun_close' => '14:00',
            'street' => 'Test Street',
            'settlement' => 'Test Settlement',
            'district' => 'Test District',
            'province' => 'Test Province',
            'region' => 'Test Region',
            'country' => 'Test Country',
            'postal_code' => '12345',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['phone_number']);

        $response = $this->postJson('/api/shops', [
            'name' => 'Test Shop',
            'email' => 'test@example.com',
            'phone_number' => '123456789', // 9 digits, should be invalid
            'mon_fri_open' => '09:00',
            'mon_fri_close' => '18:00',
            'sat_sun_open' => '10:00',
            'sat_sun_close' => '14:00',
            'street' => 'Test Street',
            'settlement' => 'Test Settlement',
            'district' => 'Test District',
            'province' => 'Test Province',
            'region' => 'Test Region',
            'country' => 'Test Country',
            'postal_code' => '12345',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['phone_number']);

        $response = $this->postJson('/api/shops', [
            'name' => 'Test Shop',
            'email' => 'test@example.com',
            'phone_number' => '12345678', // 8 digits, should be valid
            'mon_fri_open' => '09:00',
            'mon_fri_close' => '18:00',
            'sat_sun_open' => '10:00',
            'sat_sun_close' => '14:00',
            'street' => 'Test Street',
            'settlement' => 'Test Settlement',
            'district' => 'Test District',
            'province' => 'Test Province',
            'region' => 'Test Region',
            'country' => 'Test Country',
            'postal_code' => '12345',
        ]);

        $response->assertJsonMissingValidationErrors(['phone_number']);
    }

    /** @test */
    public function it_validates_time_format()
    {
        $response = $this->postJson('/api/shops', [
            'name' => 'Test Shop',
            'email' => 'test@example.com',
            'phone_number' => '12345678',
            'mon_fri_open' => 'not-a-time',
            'mon_fri_close' => '18:00',
            'sat_sun_open' => '10:00',
            'sat_sun_close' => '14:00',
            'street' => 'Test Street',
            'settlement' => 'Test Settlement',
            'district' => 'Test District',
            'province' => 'Test Province',
            'region' => 'Test Region',
            'country' => 'Test Country',
            'postal_code' => '12345',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['mon_fri_open']);
    }
}