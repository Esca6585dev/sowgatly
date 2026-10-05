<?php

namespace Tests\Feature\Admin;

use App\Models\Region;
use App\Models\Shop;

class RegionAdminTest extends AdminTestCase
{
    /** @test */
    public function list_search_and_filter_render()
    {
        $tm = Region::create(['name' => 'Türkmenistan', 'type' => 'country']);
        Region::create(['name' => 'Aşgabat', 'type' => 'city', 'parent_id' => $tm->id]);

        $this->get($this->adminUrl('region'))->assertOk()->assertSee('Aşgabat')->assertSee('Türkmenistan');
        $this->get($this->adminUrl('region?type=city'))->assertOk()->assertSee('Aşgabat')->assertDontSee('>Türkmenistan</a>', false);
        // AJAX search returns only the table partial.
        $this->get($this->adminUrl('region?search=Aşg'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Aşgabat')->assertDontSee('<html', false);
    }

    /** @test */
    public function create_edit_show_and_delete()
    {
        $parent = Region::create(['name' => 'Mary', 'type' => 'province']);

        $this->get($this->adminUrl('region/create'))->assertOk();
        $this->post($this->adminUrl('region'), ['name' => 'Baýramaly', 'type' => 'city', 'parent_id' => $parent->id])->assertRedirect();
        $region = Region::where('name', 'Baýramaly')->firstOrFail();
        $this->assertEquals($parent->id, $region->parent_id);

        Shop::factory()->create(['region_id' => $region->id, 'name' => 'Gül öýi']);
        $this->get($this->adminUrl("region/{$region->id}"))->assertOk()->assertSee('Gül öýi');
        $this->get($this->adminUrl("region/{$region->id}/edit"))->assertOk()->assertSee('Baýramaly');

        $this->put($this->adminUrl("region/{$region->id}"), ['name' => 'Baýramaly şäheri', 'type' => 'city', 'parent_id' => $parent->id])->assertRedirect();
        $this->assertEquals('Baýramaly şäheri', $region->fresh()->name);

        // A region cannot be its own parent.
        $this->put($this->adminUrl("region/{$region->id}"), ['name' => 'X', 'type' => 'city', 'parent_id' => $region->id])->assertSessionHasErrors('parent_id');

        $this->delete($this->adminUrl("region/{$region->id}"))->assertRedirect();
        $this->assertNull($region->fresh());
    }

    /** @test */
    public function validation_errors_are_shown_on_the_form()
    {
        $this->from($this->adminUrl('region/create'))->post($this->adminUrl('region'), ['name' => '', 'type' => 'planet'])
            ->assertRedirect($this->adminUrl('region/create'))
            ->assertSessionHasErrors(['name', 'type']);
    }
}
