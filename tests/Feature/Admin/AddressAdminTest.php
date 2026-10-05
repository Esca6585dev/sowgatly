<?php

namespace Tests\Feature\Admin;

use App\Models\Address;
use App\Models\Shop;

class AddressAdminTest extends AdminTestCase
{
    /** @test */
    public function list_and_ajax_search_render()
    {
        $a = Address::create(['shop_id' => Shop::factory()->create(['name' => 'Gül öýi'])->id, 'address_name' => 'Magtymguly şaýoly 12', 'postal_code' => '744000']);
        Address::create(['shop_id' => Shop::factory()->create(['name' => 'Moda House'])->id, 'address_name' => 'Mir 6/17', 'postal_code' => '746100']);

        $this->get($this->adminUrl('address'))->assertOk()->assertSee('Magtymguly şaýoly 12')->assertSee('Gül öýi')->assertSee('746100');
        $this->get($this->adminUrl('address?search=Gül'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Magtymguly')->assertDontSee('Mir 6/17')->assertDontSee('<html', false);
        $this->get($this->adminUrl('address?search=7461'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Mir 6/17')->assertDontSee('Magtymguly');
    }

    /** @test */
    public function create_store_show_edit_update_and_delete()
    {
        $shop = Shop::factory()->create(['name' => 'Gül öýi']);

        $this->get($this->adminUrl("address/create?shop_id={$shop->id}"))->assertOk()
            ->assertSee('<option value="' . $shop->id . '" selected>', false);

        $this->post($this->adminUrl('address'), ['shop_id' => $shop->id, 'address_name' => 'Magtymguly şaýoly 12', 'postal_code' => '744000'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $address = Address::where('shop_id', $shop->id)->firstOrFail();

        $this->get($this->adminUrl("address/{$address->id}"))->assertOk()->assertSee('Magtymguly şaýoly 12')->assertSee('Gül öýi');
        $this->get($this->adminUrl("address/{$address->id}/edit"))->assertOk()->assertSee('Magtymguly şaýoly 12');

        $this->put($this->adminUrl("address/{$address->id}"), ['shop_id' => $shop->id, 'address_name' => 'Bitarap Türkmenistan 5', 'postal_code' => '744001'])
            ->assertRedirect($this->adminUrl("address/{$address->id}"))->assertSessionHasNoErrors();
        $this->assertSame('Bitarap Türkmenistan 5', $address->fresh()->address_name);

        $this->delete($this->adminUrl("address/{$address->id}"))->assertRedirect($this->adminUrl('address'));
        $this->assertNull($address->fresh());
    }

    /** @test */
    public function store_validates_and_a_shop_has_one_address()
    {
        $this->from($this->adminUrl('address/create'))->post($this->adminUrl('address'), ['shop_id' => 999, 'address_name' => '', 'postal_code' => ''])
            ->assertRedirect($this->adminUrl('address/create'))
            ->assertSessionHasErrors(['shop_id', 'address_name', 'postal_code']);

        $shop = Shop::factory()->create();
        Address::create(['shop_id' => $shop->id, 'address_name' => 'A', 'postal_code' => '1']);
        $this->post($this->adminUrl('address'), ['shop_id' => $shop->id, 'address_name' => 'B', 'postal_code' => '2'])->assertSessionHasErrors('shop_id');
        $this->assertSame(1, Address::count());
    }
}
