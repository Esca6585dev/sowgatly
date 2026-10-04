<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Requests without a token (ApiTestCase always acts as a user, so this one does not extend it). */
class UnauthenticatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_routes_answer_401(): void
    {
        $this->getJson('/api/users/me')->assertStatus(401);
        $this->getJson('/api/cart')->assertStatus(401);
        $this->getJson('/api/orders')->assertStatus(401);
        $this->postJson('/api/favorites/toggle', ['product_id' => 1])->assertStatus(401);
    }
}
