<?php

namespace Tests\Feature\Admin;

use App\Models\ShopApplication;

class ShopApplicationAdminTest extends AdminTestCase
{
    /** @test */
    public function list_filters_by_status_and_search_together()
    {
        ShopApplication::create(['name' => 'Gül öýi', 'phone' => '65111111', 'status' => 'new']);
        ShopApplication::create(['name' => 'Gül bagy', 'phone' => '65222222', 'status' => 'rejected']);
        ShopApplication::create(['name' => 'Tort dünýäsi', 'phone' => '65333333', 'status' => 'new']);

        $this->get($this->adminUrl('shop-application'))->assertOk()
            ->assertSee('Gül öýi')->assertSee('Gül bagy')->assertSee('Tort dünýäsi')
            ->assertSee('class="chips"', false);

        $this->get($this->adminUrl('shop-application?status=new'))->assertOk()
            ->assertSee('Gül öýi')->assertSee('Tort dünýäsi')->assertDontSee('Gül bagy');

        // The search OR must not escape the status filter.
        $this->get($this->adminUrl('shop-application?status=new&search=Gül'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Gül öýi')->assertDontSee('Gül bagy')->assertDontSee('Tort dünýäsi')
            ->assertDontSee('<html', false);
        $this->get($this->adminUrl('shop-application?status=new&search=65222222'))->assertOk()->assertDontSee('Gül bagy');
    }

    /** @test */
    public function detail_shows_decision_form_and_create_shop_button_when_approved()
    {
        $application = ShopApplication::create(['name' => 'Gül öýi', 'phone' => '65111111', 'description' => 'Iki şahamça', 'status' => 'new']);

        $this->get($this->adminUrl("shop-application/{$application->id}"))->assertOk()
            ->assertSee('Iki şahamça')->assertSee('name="admin_note"', false)
            ->assertDontSee(route('shop.create', 'tm'), false);

        $this->put($this->adminUrl("shop-application/{$application->id}"), ['status' => 'approved', 'admin_note' => 'Jaň edildi'])
            ->assertRedirect($this->adminUrl("shop-application/{$application->id}"));

        $this->get($this->adminUrl("shop-application/{$application->id}"))->assertOk()
            ->assertSee('Jaň edildi')->assertSee(route('shop.create', 'tm'), false);
    }

    /** @test */
    public function invalid_status_is_rejected()
    {
        $application = ShopApplication::create(['name' => 'Gül öýi', 'phone' => '65111111', 'status' => 'new']);

        $this->put($this->adminUrl("shop-application/{$application->id}"), ['status' => 'maybe'])->assertSessionHasErrors('status');
        $this->assertEquals('new', $application->fresh()->status);
    }
}
