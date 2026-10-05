<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminAdminTest extends AdminTestCase
{
    /** @test */
    public function list_search_and_role_filter_render()
    {
        $editor = Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        Admin::create(['first_name' => 'Jeren', 'last_name' => 'Gurbanowa', 'username' => 'jeren', 'email' => 'jeren@example.com', 'password' => 'x'])->assignRole($editor);
        Admin::create(['first_name' => 'Serdar', 'last_name' => 'Myradow', 'username' => 'serdar', 'email' => 'serdar@example.com', 'password' => 'x']);

        $this->get($this->adminUrl('admin'))->assertOk()->assertSee('Jeren Gurbanowa')->assertSee('Serdar Myradow')->assertSee('editor');
        $this->get($this->adminUrl('admin?role=editor'))->assertOk()->assertSee('Jeren Gurbanowa')->assertDontSee('Serdar Myradow');
        $this->get($this->adminUrl('admin?search=serdar'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Serdar Myradow')->assertDontSee('Jeren Gurbanowa')->assertDontSee('<html', false);
    }

    /** @test */
    public function create_store_show_edit_update_and_delete()
    {
        $editor = Role::create(['name' => 'editor', 'guard_name' => 'admin']);
        $support = Role::create(['name' => 'support', 'guard_name' => 'admin']);

        $this->get($this->adminUrl('admin/create'))->assertOk()->assertSee('editor');

        $this->post($this->adminUrl('admin'), [
            'first_name' => 'Maýa', 'last_name' => 'Hallyýewa', 'username' => 'maya', 'email' => 'maya@example.com',
            'password' => 'secret-123', 'password_confirmation' => 'secret-123', 'roles' => ['editor'],
        ])->assertRedirect();

        $admin = Admin::where('username', 'maya')->firstOrFail();
        $this->assertTrue(Hash::check('secret-123', $admin->password));
        $this->assertTrue($admin->hasRole('editor'));

        $this->get($this->adminUrl("admin/{$admin->id}"))->assertOk()->assertSee('Maýa Hallyýewa')->assertSee('editor');
        $this->get($this->adminUrl("admin/{$admin->id}/edit"))->assertOk()->assertSee('maya@example.com')->assertSee(__('Leave empty to keep the current password'));

        // Empty password keeps the old one; roles are replaced.
        $this->put($this->adminUrl("admin/{$admin->id}"), [
            'first_name' => 'Maýa', 'last_name' => 'H.', 'username' => 'maya', 'email' => 'maya@example.com',
            'password' => '', 'password_confirmation' => '', 'roles' => ['support'],
        ])->assertRedirect($this->adminUrl("admin/{$admin->id}"));
        $admin->refresh();
        $this->assertSame('H.', $admin->last_name);
        $this->assertTrue(Hash::check('secret-123', $admin->password));
        $this->assertTrue($admin->hasRole('support'));
        $this->assertFalse($admin->hasRole('editor'));

        $this->delete($this->adminUrl("admin/{$admin->id}"))->assertRedirect($this->adminUrl('admin'));
        $this->assertNull(Admin::withTrashed()->find($admin->id));
    }

    /** @test */
    public function validation_requires_password_on_create_and_unique_username()
    {
        $this->from($this->adminUrl('admin/create'))->post($this->adminUrl('admin'), [
            'first_name' => 'A', 'last_name' => 'B', 'username' => 'admin-test', 'email' => 'admin@example.com', 'password' => '',
        ])->assertRedirect($this->adminUrl('admin/create'))->assertSessionHasErrors(['username', 'email', 'password']);

        $this->post($this->adminUrl('admin'), [
            'first_name' => 'A', 'last_name' => 'B', 'username' => 'ab', 'email' => 'ab@example.com',
            'password' => 'secret-123', 'password_confirmation' => 'other-123',
        ])->assertSessionHasErrors('password');
    }

    /** @test */
    public function an_admin_cannot_delete_themselves()
    {
        $this->get($this->adminUrl("admin/{$this->admin->id}"))->assertOk()->assertDontSee('admin/' . $this->admin->id . '" data-confirm', false);

        $this->delete($this->adminUrl("admin/{$this->admin->id}"))->assertRedirect()->assertSessionHas('warning');
        $this->assertNotNull($this->admin->fresh());
    }
}
