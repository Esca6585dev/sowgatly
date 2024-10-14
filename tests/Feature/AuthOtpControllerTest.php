<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserOtp;
use Laravel\Sanctum\Sanctum;

class AuthOtpControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_generate_otp()
    {
        $user = User::factory()->create([
            'phone_number' => '65123456',
        ]);

        $response = $this->postJson('/api/otp/generate', [
            'phone_number' => '65123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'OTP has been sent on Your Mobile Number.',
            ]);

        $this->assertDatabaseHas('user_otps', [
            'user_id' => $user->id,
        ]);
    }

    public function test_login_with_otp()
    {
        $user = User::factory()->create([
            'phone_number' => '65123456',
        ]);

        UserOtp::create([
            'user_id' => $user->id,
            'otp' => '0000',
            'expire_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/otp/login', [
            'phone_number' => '65123456',
            'otp' => '0000',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'access_token',
                'token_type',
                'user',
                'shops',
            ]);
    }

    public function test_register_with_otp()
    {
        $response = $this->postJson('/api/otp/register', [
            'phone_number' => '65123456',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'access_token',
                'token_type',
                'otp',
                'user',
                'shops',
            ]);

        $this->assertDatabaseHas('users', [
            'phone_number' => '65123456',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_logout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/otp/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
    }
}