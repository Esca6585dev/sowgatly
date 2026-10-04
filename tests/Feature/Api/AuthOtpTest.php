<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserOtp;

class AuthOtpTest extends ApiTestCase
{
    public function test_generate_creates_a_code_for_an_existing_user(): void
    {
        $this->postJson('/api/otp/generate', ['phone_number' => $this->user->phone_number])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_otps', ['user_id' => $this->user->id, 'otp' => '0000']);
    }

    public function test_generate_tells_unknown_numbers_to_register(): void
    {
        $response = $this->postJson('/api/otp/generate', ['phone_number' => '61111111'])
            ->assertOk()
            ->assertJson(['success' => false]);

        $this->assertStringContainsStringIgnoringCase('not found', $response->json('message'));
    }

    public function test_generate_rejects_a_non_turkmen_number(): void
    {
        // Validation failures on the OTP endpoints answer 200 with success=false.
        $this->postJson('/api/otp/generate', ['phone_number' => '+99365123456'])
            ->assertOk()
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('user_otps', 0);
    }

    public function test_login_with_a_valid_code_returns_a_bearer_token(): void
    {
        UserOtp::create(['user_id' => $this->user->id, 'otp' => '0000', 'expire_at' => now()->addMinutes(10)]);

        $response = $this->postJson('/api/login', ['phone_number' => $this->user->phone_number, 'otp' => '0000'])
            ->assertOk()
            ->assertJsonStructure(['success', 'access_token', 'token_type', 'user' => ['id', 'name', 'phone_number'], 'shops']);

        $this->assertSame('Bearer', $response->json('token_type'));

        // The token works for authenticated routes.
        $this->withToken($response->json('access_token'))
            ->getJson('/api/users/me')
            ->assertOk()
            ->assertJsonPath('data.id', $this->user->id);
    }

    public function test_login_with_a_wrong_code_fails(): void
    {
        UserOtp::create(['user_id' => $this->user->id, 'otp' => '0000', 'expire_at' => now()->addMinutes(10)]);

        $this->postJson('/api/login', ['phone_number' => $this->user->phone_number, 'otp' => '1234'])
            ->assertJson(['success' => false]);
    }

    public function test_register_creates_the_user_and_echoes_the_debug_code(): void
    {
        $response = $this->postJson('/api/register', [
            'phone_number' => '65123456',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->assertOk()->assertJson(['success' => true, 'otp' => '0000']);

        $this->assertDatabaseHas('users', ['phone_number' => '65123456', 'name' => 'Test User']);
        $this->assertNotEmpty($response->json('access_token'));
    }

    public function test_register_rejects_a_duplicate_phone(): void
    {
        $this->postJson('/api/register', ['phone_number' => $this->user->phone_number, 'name' => 'Dup'])
            ->assertOk()
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_logout_revokes_the_token(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_protected_routes_require_a_token(): void
    {
        $this->getJson('/api/users/me')->assertStatus(401);
        $this->getJson('/api/cart')->assertStatus(401);
    }
}
