<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Region;
use App\Models\Shop;

class CatalogTest extends ApiTestCase
{
    public function test_categories_are_localized(): void
    {
        $parent = Category::factory()->create(['name_tm' => 'Güller', 'name_ru' => 'Цветы', 'name_en' => 'Flowers']);
        Category::factory()->count(2)->create(['category_id' => $parent->id]);

        $this->actingAsCustomer()
            ->getJson('/api/categories')
            ->assertOk()
            ->assertJsonPath('data.0.name.tm', 'Güller')
            ->assertJsonPath('data.0.name.ru', 'Цветы');

        $this->getJson("/api/categories/{$parent->id}/subcategories")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_product_show_returns_the_full_card(): void
    {
        $shop = $this->shopWithProducts(1);
        $product = $this->productsOf($shop)->first();

        $this->actingAsCustomer()
            ->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonStructure(['data' => [
                'id', 'name' => ['tm', 'ru', 'en'], 'price', 'discount', 'description' => ['tm', 'ru', 'en'],
                'production_time', 'stock', 'shop_id', 'category_id', 'images', 'category', 'shop',
            ]])
            ->assertJsonCount(1, 'data.images');
    }

    public function test_product_show_404_for_unknown_id(): void
    {
        $this->actingAsCustomer()->getJson('/api/products/999')->assertStatus(404);
    }

    public function test_search_filters_by_name_and_region_and_paginates(): void
    {
        $ashgabat = $this->cityRegion('Aşgabat');
        $mary = $this->cityRegion('Mary');
        $shopA = $this->shopWithProducts(2, $ashgabat);
        $this->shopWithProducts(2, $mary);

        $this->productsOf($shopA)->first()->update(['name_tm' => 'Gyzyl bägül', 'name_ru' => 'Красная роза', 'name_en' => 'Red rose']);

        $this->actingAsCustomer()
            ->getJson("/api/product/search?region_id={$ashgabat->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['success', 'data', 'meta' => ['current_page', 'last_page', 'total']]);

        $this->getJson('/api/product/search?name=роза')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_search_sorts_by_price(): void
    {
        $shop = $this->shopWithProducts(3);
        $prices = [300, 100, 200];
        foreach ($this->productsOf($shop) as $i => $product) {
            $product->update(['price' => $prices[$i]]);
        }

        $data = $this->actingAsCustomer()->getJson('/api/product/search?sort=price_asc')->json('data');

        $this->assertSame(['100.00', '200.00', '300.00'], array_column($data, 'price'));
    }

    public function test_products_by_category(): void
    {
        $category = Category::factory()->create();
        $this->shopWithProducts(2, null, $category);
        $this->shopWithProducts(1);

        $this->actingAsCustomer()
            ->getJson("/api/product/category/{$category->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_shops_index_lists_only_the_callers_shops_and_show_is_open(): void
    {
        $own = Shop::factory()->create(['user_id' => $this->user->id]);
        $other = Shop::factory()->create();

        $this->actingAsCustomer()
            ->getJson('/api/shops')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id);

        $this->getJson("/api/shops/{$other->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $other->id)
            ->assertJsonStructure(['data' => ['id', 'name', 'mon_fri_open', 'mon_fri_close', 'sat_sun_open', 'sat_sun_close', 'image']]);
    }

    public function test_regions_and_children(): void
    {
        $city = $this->cityRegion();
        $province = $city->parent;

        $this->actingAsCustomer()
            ->getJson('/api/regions')
            ->assertOk()
            ->assertJsonFragment(['id' => $city->id, 'type' => 'city']);

        $this->getJson("/api/regions/parent/{$province->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $city->id);
    }

    public function test_reference_data_is_read_only(): void
    {
        $this->actingAsCustomer();

        $this->postJson('/api/compositions', ['name' => 'Rose'])->assertStatus(405);
        $this->postJson('/api/categories', ['name_tm' => 'x'])->assertStatus(405);
        $this->postJson('/api/regions', ['name' => 'x'])->assertStatus(405);
        $this->postJson('/api/brands', ['name' => 'x'])->assertStatus(405);
    }
}
