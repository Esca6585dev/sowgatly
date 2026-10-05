<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Shop;

/**
 * Index, AJAX search, filters, show, edit and delete pages of the product
 * section. Create/update writes are covered by AdminProductTest.
 */
class ProductAdminPagesTest extends AdminTestCase
{
    private function product(array $attributes = []): Product
    {
        $product = Product::factory()->create(array_merge(['name_tm' => 'Gül çemeni', 'name_en' => 'Rose bouquet', 'name_ru' => 'Букет роз'], $attributes));
        $product->images()->delete(); // the factory adds three seeder images

        return $product;
    }

    /** @test */
    public function index_lists_products_with_thumbnail_shop_and_discounted_price()
    {
        $product = $this->product(['price' => 200, 'discount' => 10]);
        Image::create(['product_id' => $product->id, 'url' => 'product/test/cover.jpg']);

        $this->get($this->adminUrl('product'))->assertOk()
            ->assertSee('Gül çemeni')
            ->assertSee($product->shop->name)
            ->assertSee('180 TMT')
            ->assertSee('product/test/cover.jpg')
            ->assertSee('data-table-form', false)
            ->assertDontSee('metronic', false);
    }

    /** @test */
    public function ajax_search_returns_only_the_table_partial()
    {
        $this->product();
        $this->product(['name_tm' => 'Tort', 'name_en' => 'Cake', 'name_ru' => 'Торт']);

        $this->get($this->adminUrl('product?search=Rose'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Gül çemeni')->assertDontSee('>Tort<', false)->assertDontSee('<html', false);
    }

    /** @test */
    public function filters_by_shop_category_and_status()
    {
        $shop = Shop::factory()->create();
        $parent = Category::factory()->create(['category_id' => null]);
        $child = Category::factory()->create(['category_id' => $parent->id]);
        $a = $this->product(['shop_id' => $shop->id, 'category_id' => $child->id, 'name_tm' => 'Birinji haryt']);
        $b = $this->product(['name_tm' => 'Ikinji haryt', 'status' => false]);
        $c = $this->product(['name_tm' => 'Üçünji haryt', 'seller_status' => false]);

        $ajax = ['X-Requested-With' => 'XMLHttpRequest'];
        $this->get($this->adminUrl("product?shop_id={$shop->id}"), $ajax)->assertSee('Birinji haryt')->assertDontSee('Ikinji haryt');
        // A parent category also finds products of its subcategories.
        $this->get($this->adminUrl("product?category_id={$parent->id}"), $ajax)->assertSee('Birinji haryt')->assertDontSee('Ikinji haryt');
        $this->get($this->adminUrl('product?status=inactive'), $ajax)->assertSee('Ikinji haryt')->assertDontSee('Birinji haryt');
        $this->get($this->adminUrl('product?status=active'), $ajax)->assertSee('Birinji haryt')->assertDontSee('Ikinji haryt');
        $this->get($this->adminUrl('product?status=hidden'), $ajax)->assertSee('Üçünji haryt')->assertDontSee('Birinji haryt');
    }

    /** @test */
    public function show_page_has_gallery_facts_attributes_and_links()
    {
        $product = $this->product(['price' => 150, 'discount' => 20, 'stock' => 7]);
        Image::create(['product_id' => $product->id, 'url' => 'product/test/one.jpg']);
        Image::create(['product_id' => $product->id, 'url' => 'product/test/two.jpg']);
        ProductAttribute::create(['product_id' => $product->id, 'attribute_key' => 'color', 'attribute_value' => json_encode(['red', 'white'])]);

        $this->get($this->adminUrl("product/{$product->id}"))->assertOk()
            ->assertSee('Gül çemeni')->assertSee('Букет роз')->assertSee('Rose bouquet')
            ->assertSee('product/test/one.jpg')->assertSee('product/test/two.jpg')
            ->assertSee('120 TMT')
            ->assertSee('white')
            ->assertSee(route('shop.show', ['tm', $product->shop_id]), false)
            ->assertSee(route('product.edit', ['tm', $product->id]), false);
    }

    /** @test */
    public function create_and_edit_forms_render_with_current_values()
    {
        $product = $this->product(['price' => 99.5]);
        Image::create(['product_id' => $product->id, 'url' => 'product/test/current.jpg']);

        $this->get($this->adminUrl('product/create'))->assertOk()
            ->assertSee('name="name_tm"', false)->assertSee('name="images[]"', false)->assertSee('enctype="multipart/form-data"', false);

        $this->get($this->adminUrl("product/{$product->id}/edit"))->assertOk()
            ->assertSee('Gül çemeni')->assertSee('value="99.50"', false)
            ->assertSee('product/test/current.jpg')
            ->assertSee('name="_method" value="put"', false);
    }

    /** @test */
    public function update_keeps_images_when_none_are_uploaded()
    {
        $product = $this->product();
        Image::create(['product_id' => $product->id, 'url' => 'product/product-seeder/keep.jpg']);

        $this->put($this->adminUrl("product/{$product->id}"), array_merge($product->only([
            'name_tm', 'name_en', 'name_ru', 'description_tm', 'description_en', 'description_ru', 'shop_id', 'category_id',
        ]), ['price' => '10', 'status' => '1', 'seller_status' => '0']))->assertRedirect($this->adminUrl('product'));

        $this->assertSame(['product/product-seeder/keep.jpg'], $product->images()->pluck('url')->all());
        $this->assertFalse($product->fresh()->seller_status);
    }

    /** @test */
    public function destroy_deletes_the_product_and_its_image_rows()
    {
        $product = $this->product();
        Image::create(['product_id' => $product->id, 'url' => 'product/product-seeder/shared.jpg']);

        $this->delete($this->adminUrl("product/{$product->id}"))
            ->assertRedirect($this->adminUrl('product'))->assertSessionHas('success-delete');

        $this->assertNull($product->fresh());
        $this->assertDatabaseCount('images', 0);
    }
}
