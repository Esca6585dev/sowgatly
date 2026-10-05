<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Signed-in admin for admin panel feature tests. URLs use the "tm" locale.
 */
abstract class AdminTestCase extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create([
            'first_name' => 'Admin', 'last_name' => 'Test', 'username' => 'admin-test',
            'email' => 'admin@example.com', 'password' => bcrypt('secret'),
        ]);
        $this->actingAs($this->admin, 'admin');
    }

    /** Admin URL helper: $this->admin('region/5/edit') -> /tm/admin/region/5/edit */
    protected function adminUrl(string $path): string
    {
        return '/tm/admin/' . ltrim($path, '/');
    }
}
