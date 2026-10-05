<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\AdminControllers\Shop\ShopController;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Region;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ShopAdminTest extends AdminTestCase
{
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Gül öýi', 'email' => 'gul@example.com', 'phone' => '65656585',
            'mon_fri_open' => '09:00', 'mon_fri_close' => '18:00', 'sat_sun_open' => '10:00', 'sat_sun_close' => '16:00',
            'delivery_fee' => '25', 'min_order_amount' => '', 'pickup_available' => '1', 'status' => 'pending',
            'description_tm' => 'Gül dükany',
        ], $overrides);
    }

    /** @test */
    public function list_search_and_filters_render()
    {
        $mary = Region::create(['name' => 'Mary', 'type' => 'city']);
        $owner = User::factory()->create(['name' => 'Aýna Owezowa']);
        $shop = Shop::factory()->create(['name' => 'Gül öýi', 'status' => 'pending', 'region_id' => $mary->id, 'user_id' => $owner->id]);
        Product::factory()->count(2)->create(['shop_id' => $shop->id]);
        Shop::factory()->create(['name' => 'Moda House', 'status' => 'approved']);

        $this->get($this->adminUrl('shop'))->assertOk()->assertSee('Gül öýi')->assertSee('Moda House')->assertSee('Aýna Owezowa')
            ->assertSee('/shop/shop-seeder/', false);
        $this->get($this->adminUrl('shop?status=pending'))->assertOk()->assertSee('Gül öýi')->assertDontSee('<b>Moda House</b>', false);
        $this->get($this->adminUrl("shop?region_id={$mary->id}"))->assertOk()->assertSee('Gül öýi')->assertDontSee('<b>Moda House</b>', false);

        // AJAX search (also by owner name) returns only the table partial.
        $this->get($this->adminUrl('shop?search=Owezowa'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Gül öýi')->assertDontSee('Moda House')->assertDontSee('<html', false);
    }

    /** @test */
    public function create_store_with_logo_on_the_public_disk()
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $region = Region::create(['name' => 'Aşgabat', 'type' => 'city']);

        $this->get($this->adminUrl('shop/create'))->assertOk()->assertSee('name="mon_fri_open"', false)->assertSee('name="user_id"', false);

        $this->post($this->adminUrl('shop'), $this->payload([
            'user_id' => $owner->id, 'region_id' => $region->id, 'image' => UploadedFile::fake()->image('logo.png'),
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $shop = Shop::where('name', 'Gül öýi')->firstOrFail();
        $this->assertSame('pending', $shop->status);
        $this->assertSame($owner->id, $shop->user_id);
        $this->assertTrue($shop->pickup_available);
        $this->assertEquals(25, $shop->delivery_fee);
        $this->assertNull($shop->min_order_amount);
        $this->assertStringStartsWith('shops/', $shop->image);
        Storage::disk('public')->assertExists($shop->image);
        // Same URL the API builds (ShopResource: asset('storage/' . $image)).
        $this->assertSame(asset('storage/' . $shop->image), ShopController::imageUrl($shop->image));
        $this->assertSame(asset('shop/shop-seeder/logo.png'), ShopController::imageUrl('shop/shop-seeder/logo.png'));
    }

    /** @test */
    public function store_validates_and_one_user_owns_one_shop()
    {
        $this->from($this->adminUrl('shop/create'))
            ->post($this->adminUrl('shop'), $this->payload(['name' => '', 'mon_fri_close' => '08:00', 'status' => 'closed']))
            ->assertRedirect($this->adminUrl('shop/create'))
            ->assertSessionHasErrors(['name', 'mon_fri_close', 'status']);

        $taken = Shop::factory()->create();
        $this->post($this->adminUrl('shop'), $this->payload(['user_id' => $taken->user_id]))->assertSessionHasErrors('user_id');
        $this->assertSame(1, Shop::count());
    }

    /** @test */
    public function show_page_lists_details_products_and_orders()
    {
        $shop = Shop::factory()->create(['name' => 'Gül öýi', 'mon_fri_open' => '09:00:00', 'mon_fri_close' => '18:30']);
        Address::create(['shop_id' => $shop->id, 'address_name' => 'Magtymguly şaýoly 12', 'postal_code' => '744000']);
        $product = Product::factory()->create(['shop_id' => $shop->id, 'name_tm' => 'Gyzyl gül']);
        $order = Order::create([
            'user_id' => User::factory()->create()->id, 'shop_id' => $shop->id, 'total_amount' => 120, 'items_total' => 100,
            'delivery_fee' => 20, 'status' => 'pending', 'delivery_type' => 'asap', 'recipient_phone' => '65656585',
            'delivery_address' => 'x', 'payment_method' => 'cash',
        ]);

        $this->get($this->adminUrl("shop/{$shop->id}"))->assertOk()
            ->assertSee('Gül öýi')->assertSee('Magtymguly şaýoly 12')->assertSee('09:00 – 18:30')
            ->assertSee('Gyzyl gül')->assertSee($order->number)
            ->assertSee('value="rejected"', false);
    }

    /** @test */
    public function edit_preselects_stored_hours_and_update_saves()
    {
        $shop = Shop::factory()->create(['mon_fri_open' => '08:30:00', 'sat_sun_close' => '20:15']);

        $this->get($this->adminUrl("shop/{$shop->id}/edit"))->assertOk()
            ->assertSee('<option value="08:30" selected>', false)
            ->assertSee('<option value="20:15" selected>', false);

        $this->put($this->adminUrl("shop/{$shop->id}"), $this->payload(['user_id' => $shop->user_id, 'region_id' => $shop->region_id, 'pickup_available' => '0', 'status' => 'approved']))
            ->assertRedirect($this->adminUrl("shop/{$shop->id}"))->assertSessionHasNoErrors();

        $shop->refresh();
        $this->assertSame('Gül öýi', $shop->name);
        $this->assertFalse($shop->pickup_available);
        $this->assertSame('09:00', $shop->mon_fri_open);
    }

    /** @test */
    public function quick_status_change_keeps_the_other_fields()
    {
        $shop = Shop::factory()->create(['name' => 'Moda House', 'status' => 'pending', 'delivery_fee' => 15, 'pickup_available' => true]);

        $this->put($this->adminUrl("shop/{$shop->id}"), ['status' => 'approved'])->assertRedirect()->assertSessionHasNoErrors();
        $shop->refresh();
        $this->assertSame('approved', $shop->status);
        $this->assertSame('Moda House', $shop->name);
        $this->assertEquals(15, $shop->delivery_fee);
        $this->assertTrue($shop->pickup_available);

        $this->put($this->adminUrl("shop/{$shop->id}"), ['status' => 'rejected'])->assertRedirect();
        $this->assertSame('rejected', $shop->fresh()->status);
    }

    /** @test */
    public function destroy_deletes_the_shop_and_its_uploaded_logo()
    {
        Storage::fake('public');
        $shop = Shop::factory()->create(['image' => UploadedFile::fake()->image('a.png')->store('shops', 'public')]);

        $this->delete($this->adminUrl("shop/{$shop->id}"))->assertRedirect($this->adminUrl('shop'));
        $this->assertNull($shop->fresh());
        Storage::disk('public')->assertMissing($shop->image);
    }
}
