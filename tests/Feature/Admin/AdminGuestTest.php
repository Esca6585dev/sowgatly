<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGuestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guests opening admin pages are sent to the admin login; the API answers
     * with a JSON 401.
     *
     * @test
     */
    public function guests_are_redirected_to_the_admin_login()
    {
        foreach (['/tm/admin/dashboard', '/tm/admin/order', '/tm/admin/chat', '/tm/admin/shop-application', '/ru/admin/product'] as $url) {
            $this->get($url)->assertRedirect('/' . substr($url, 1, 2) . '/admin/login');
        }

        $this->getJson('/api/orders')->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }
}
