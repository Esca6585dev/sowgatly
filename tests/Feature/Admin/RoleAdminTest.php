<?php

namespace Tests\Feature\Admin;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAdminTest extends AdminTestCase
{
    /** @test */
    public function list_and_search_render()
    {
        Role::create(['name' => 'content-manager', 'guard_name' => 'admin']);
        Role::create(['name' => 'support', 'guard_name' => 'admin']);

        $this->get($this->adminUrl('role'))->assertOk()->assertSee('content-manager')->assertSee('support');
        $this->get($this->adminUrl('role?search=supp'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('support')->assertDontSee('content-manager')->assertDontSee('<html', false);
    }

    /** @test */
    public function create_store_show_edit_update_and_delete()
    {
        foreach (['banner-list', 'banner-create', 'order-list', 'order-edit'] as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'admin']);
        }

        // The form groups permissions by prefix.
        $this->get($this->adminUrl('role/create'))->assertOk()->assertSeeInOrder(['banner', 'banner-create', 'banner-list', 'order', 'order-edit']);

        $this->post($this->adminUrl('role'), ['name' => 'content-manager', 'permissions' => ['banner-list', 'banner-create']])->assertRedirect();
        $role = Role::findByName('content-manager', 'admin');
        $this->assertEqualsCanonicalizing(['banner-list', 'banner-create'], $role->permissions->pluck('name')->all());

        $this->admin->assignRole($role);
        $this->get($this->adminUrl("role/{$role->id}"))->assertOk()->assertSee('banner-create')->assertSee('Admin Test');
        $this->get($this->adminUrl("role/{$role->id}/edit"))->assertOk()->assertSee('value="banner-list" checked', false);

        $this->put($this->adminUrl("role/{$role->id}"), ['name' => 'orders', 'permissions' => ['order-list']])->assertRedirect($this->adminUrl("role/{$role->id}"));
        $role->refresh();
        $this->assertSame('orders', $role->name);
        $this->assertSame(['order-list'], $role->permissions->pluck('name')->all());

        $this->delete($this->adminUrl("role/{$role->id}"))->assertRedirect($this->adminUrl('role'));
        $this->assertNull(Role::find($role->id));
    }

    /** @test */
    public function validation_rejects_duplicates_and_unknown_permissions()
    {
        Role::create(['name' => 'support', 'guard_name' => 'admin']);

        $this->from($this->adminUrl('role/create'))->post($this->adminUrl('role'), ['name' => 'support', 'permissions' => ['does-not-exist']])
            ->assertRedirect($this->adminUrl('role/create'))
            ->assertSessionHasErrors(['name', 'permissions.0']);
    }
}
