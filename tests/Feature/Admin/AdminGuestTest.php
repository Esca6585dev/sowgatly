<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGuestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The project's exception handler answers every AuthenticationException with
     * a JSON "Unauthenticated." body (status 200) instead of redirecting, so the
     * check here is that no page content leaks, not the status code.
     *
     * @test
     */
    public function guests_do_not_see_the_new_admin_pages()
    {
        foreach (['/tm/admin/order', '/tm/admin/chat', '/tm/admin/shop-application'] as $url) {
            $this->get($url)
                ->assertDontSee('<table', false)
                ->assertSee('Unauthenticated');
        }
    }
}
