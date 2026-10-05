<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Failure cases sent without a bearer token (ApiTestCase always signs a
 * user in, so this class extends Tests\TestCase).
 */
class ContractGuestFailuresTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_without_a_token_answers_401(): void
    {
        $this->postJson('/api/logout')->assertStatus(401);
    }

    public function test_marking_notifications_read_without_a_token_answers_401(): void
    {
        $this->postJson('/api/me/notifications/read')->assertStatus(401);
    }

    public function test_removing_the_profile_image_without_a_token_answers_401(): void
    {
        $this->deleteJson('/api/users/me/image')->assertStatus(401);
    }
}
