<?php

namespace Tests\Feature\Admin;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionAdminTest extends AdminTestCase
{
    /** @test */
    public function list_search_and_section_filter_render()
    {
        Permission::create(['name' => 'banner-list', 'guard_name' => 'admin']);
        Permission::create(['name' => 'order-edit', 'guard_name' => 'admin']);

        $this->get($this->adminUrl('permission'))->assertOk()->assertSee('banner-list')->assertSee('order-edit');
        $this->get($this->adminUrl('permission?group=order'))->assertOk()->assertSee('order-edit')->assertDontSee('>banner-list</a>', false);
        $this->get($this->adminUrl('permission?search=bann'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('banner-list')->assertDontSee('order-edit')->assertDontSee('<html', false);
    }

    /** @test */
    public function create_store_show_edit_update_and_delete()
    {
        $this->get($this->adminUrl('permission/create'))->assertOk();

        $this->post($this->adminUrl('permission'), ['name' => ' banner-create '])->assertRedirect();
        $permission = Permission::where('name', 'banner-create')->firstOrFail();
        $this->assertSame('admin', $permission->guard_name);

        Role::create(['name' => 'content-manager', 'guard_name' => 'admin'])->givePermissionTo($permission);
        $this->get($this->adminUrl("permission/{$permission->id}"))->assertOk()->assertSee('content-manager');
        $this->get($this->adminUrl("permission/{$permission->id}/edit"))->assertOk()->assertSee('banner-create');

        $this->put($this->adminUrl("permission/{$permission->id}"), ['name' => 'banner-edit'])->assertRedirect($this->adminUrl("permission/{$permission->id}"));
        $this->assertSame('banner-edit', $permission->fresh()->name);

        $this->delete($this->adminUrl("permission/{$permission->id}"))->assertRedirect($this->adminUrl('permission'));
        $this->assertNull(Permission::find($permission->id));
    }

    /** @test */
    public function validation_rejects_empty_and_duplicate_names()
    {
        Permission::create(['name' => 'banner-list', 'guard_name' => 'admin']);

        $this->from($this->adminUrl('permission/create'))->post($this->adminUrl('permission'), ['name' => ''])
            ->assertRedirect($this->adminUrl('permission/create'))->assertSessionHasErrors('name');
        $this->post($this->adminUrl('permission'), ['name' => 'banner-list'])->assertSessionHasErrors('name');
    }
}
