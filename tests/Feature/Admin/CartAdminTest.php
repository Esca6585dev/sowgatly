<?php

namespace Tests\Feature\Admin;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Image;
use App\Models\Product;
use App\Models\User;

class CartAdminTest extends AdminTestCase
{
    private function cartWithItems(string $customer = 'Merjen Annaýewa'): Cart
    {
        $user = User::factory()->create(['name' => $customer, 'phone_number' => '61234567']);
        $cart = Cart::create(['user_id' => $user->id]);
        $rose = Product::factory()->create(['name_tm' => 'Gyzyl gül', 'price' => 50]);
        $cake = Product::factory()->create(['name_tm' => 'Şokolad tort', 'price' => 300]);
        $rose->images()->delete(); // replace the factory images with a known cover
        Image::create(['product_id' => $rose->id, 'url' => 'product/test/rose.jpg']);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $rose->id, 'quantity' => 3, 'price' => 50]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $cake->id, 'quantity' => 1, 'price' => 300]);

        return $cart;
    }

    /** @test */
    public function index_lists_carts_with_customer_items_and_total()
    {
        $cart = $this->cartWithItems();
        Cart::create(['user_id' => User::factory()->create(['name' => 'Boş Sebet'])->id]);

        $this->get($this->adminUrl('cart'))->assertOk()
            ->assertSee('Merjen Annaýewa')->assertSee('+993 61234567')
            ->assertSee('450 TMT')
            ->assertSee(route('cart.show', ['tm', $cart->id]), false)
            ->assertSee('Boş Sebet');
    }

    /** @test */
    public function ajax_search_and_state_filter_return_the_partial()
    {
        $this->cartWithItems();
        Cart::create(['user_id' => User::factory()->create(['name' => 'Boş Sebet'])->id]);
        $ajax = ['X-Requested-With' => 'XMLHttpRequest'];

        $this->get($this->adminUrl('cart?search=Merjen'), $ajax)->assertOk()
            ->assertSee('Merjen Annaýewa')->assertDontSee('Boş Sebet')->assertDontSee('<html', false);
        $this->get($this->adminUrl('cart?search=61234567'), $ajax)->assertSee('Merjen Annaýewa');
        $this->get($this->adminUrl('cart?state=empty'), $ajax)->assertSee('Boş Sebet')->assertDontSee('Merjen Annaýewa');
        $this->get($this->adminUrl('cart?state=filled'), $ajax)->assertSee('Merjen Annaýewa')->assertDontSee('Boş Sebet');
    }

    /** @test */
    public function show_lists_items_with_thumbnails_and_sums()
    {
        $cart = $this->cartWithItems();

        $this->get($this->adminUrl("cart/{$cart->id}"))->assertOk()
            ->assertSee('Gyzyl gül')->assertSee('Şokolad tort')
            ->assertSee('product/test/rose.jpg')
            ->assertSee('150 TMT')   // 3 × 50
            ->assertSee('450 TMT')   // total
            ->assertSee('Merjen Annaýewa');
    }

    /** @test */
    public function create_and_edit_redirect_instead_of_showing_a_form()
    {
        $cart = $this->cartWithItems();

        $this->get($this->adminUrl('cart/create'))->assertRedirect($this->adminUrl('cart'));
        $this->post($this->adminUrl('cart'), [])->assertRedirect($this->adminUrl('cart'));
        $this->get($this->adminUrl("cart/{$cart->id}/edit"))->assertRedirect($this->adminUrl("cart/{$cart->id}"));
        $this->put($this->adminUrl("cart/{$cart->id}"), [])->assertRedirect($this->adminUrl("cart/{$cart->id}"));
        $this->assertDatabaseCount('carts', 1);
    }

    /** @test */
    public function destroy_deletes_the_cart_and_its_items()
    {
        $cart = $this->cartWithItems();

        $this->delete($this->adminUrl("cart/{$cart->id}"))
            ->assertRedirect($this->adminUrl('cart'))->assertSessionHas('success-delete');

        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('products', 2);
    }

    /** @test */
    public function a_missing_cart_is_a_404()
    {
        $this->get($this->adminUrl('cart/999'))->assertNotFound();
    }
}
