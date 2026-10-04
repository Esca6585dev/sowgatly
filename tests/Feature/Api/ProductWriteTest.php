<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

/**
 * Shop owners create, update and delete the products of their own shop
 * through /api/products, using the per-language columns of the table.
 */
class ProductWriteTest extends ApiTestCase
{
    protected Shop $shop;

    protected Category $category;

    protected Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shop = Shop::factory()->create(['user_id' => $this->user->id]);
        $this->category = Category::factory()->create();
        $this->brand = Brand::factory()->create();
    }

    private function validProduct(array $overrides = []): array
    {
        return array_merge([
            'name_tm' => 'Bägül çemeni',
            'name_en' => 'Rose bouquet',
            'name_ru' => 'Букет роз',
            'description_tm' => 'Gyzyl bägüller',
            'description_en' => 'Red roses',
            'description_ru' => 'Красные розы',
            'price' => 250.5,
            'discount' => 10,
            'stock' => 20,
            'production_time' => 120,
            'min_order' => 1,
            'seller_status' => true,
            'status' => true,
            'category_id' => $this->category->id,
            'brand_ids' => [$this->brand->id],
        ], $overrides);
    }

    /** @test */
    public function a_shop_owner_can_create_a_product()
    {
        $response = $this->postJson('/api/products', $this->validProduct());

        $response->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Product created successfully'])
            ->assertJsonPath('data.name.en', 'Rose bouquet')
            ->assertJsonPath('data.shop_id', $this->shop->id)
            ->assertJsonPath('product.id', $response->json('data.id'))
            ->assertJsonPath('data.brands.0.id', $this->brand->id);

        $this->assertDatabaseHas('products', [
            'name_tm' => 'Bägül çemeni',
            'name_en' => 'Rose bouquet',
            'price' => 250.5,
            'stock' => 20,
            'shop_id' => $this->shop->id,
        ]);
        $this->assertDatabaseHas('brands_products', ['brands_id' => $this->brand->id]);
    }

    /** @test */
    public function the_product_always_belongs_to_the_callers_shop()
    {
        $otherShop = Shop::factory()->create();

        $this->postJson('/api/products', $this->validProduct(['shop_id' => $otherShop->id]))->assertStatus(201);

        $this->assertDatabaseHas('products', ['name_en' => 'Rose bouquet', 'shop_id' => $this->shop->id]);
        $this->assertDatabaseMissing('products', ['shop_id' => $otherShop->id]);
    }

    /** @test */
    public function users_without_a_shop_cannot_create_products()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/products', $this->validProduct())->assertStatus(403)->assertJson(['success' => false]);
        $this->assertDatabaseCount('products', 0);
    }

    /** @test */
    public function creating_validates_the_per_language_fields_and_numbers()
    {
        $this->postJson('/api/products', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name_tm', 'name_en', 'name_ru',
                'description_tm', 'description_en', 'description_ru',
                'price', 'seller_status', 'status', 'category_id',
            ]);

        $this->postJson('/api/products', $this->validProduct(['price' => 'abc', 'discount' => 150]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price', 'discount']);
    }

    /** @test */
    public function a_shop_owner_can_partially_update_their_product()
    {
        $product = Product::factory()->create(['shop_id' => $this->shop->id]);
        $product->brands()->attach($this->brand->id);

        $this->putJson("/api/products/{$product->id}", ['name_en' => 'Updated name', 'price' => 99.99])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Product updated successfully'])
            ->assertJsonPath('data.name.en', 'Updated name');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name_en' => 'Updated name', 'price' => 99.99]);
        // brand_ids was not sent, so the brands stay.
        $this->assertDatabaseHas('brands_products', ['products_id' => $product->id, 'brands_id' => $this->brand->id]);

        $this->putJson("/api/products/{$product->id}", ['brand_ids' => []])->assertStatus(200);
        $this->assertDatabaseMissing('brands_products', ['products_id' => $product->id]);
    }

    /** @test */
    public function updating_stores_uploaded_images_in_the_url_column()
    {
        $product = Product::factory()->create(['shop_id' => $this->shop->id]);
        $before = $product->images()->count();
        $png = 'data:image/png;base64,'.base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));

        $this->putJson("/api/products/{$product->id}", ['images' => [$png]])->assertStatus(200);

        $this->assertSame($before + 1, $product->images()->count());
        $this->assertNotNull($product->images()->latest('id')->first()->url);
    }

    /** @test */
    public function other_shops_products_cannot_be_updated_or_deleted()
    {
        $product = Product::factory()->create();

        $this->putJson("/api/products/{$product->id}", ['name_en' => 'Hijacked'])->assertStatus(403);
        $this->deleteJson("/api/products/{$product->id}")->assertStatus(403);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name_en' => $product->name_en]);
    }

    /** @test */
    public function a_shop_owner_can_delete_their_product()
    {
        $product = Product::factory()->create(['shop_id' => $this->shop->id]);

        $this->deleteJson("/api/products/{$product->id}")
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Product deleted successfully']);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('images', ['product_id' => $product->id]);
    }

    /** @test */
    public function search_returns_the_brands_of_each_product()
    {
        $product = Product::factory()->create(['shop_id' => $this->shop->id, 'status' => true, 'name_en' => 'Branded tulips']);
        $product->brands()->attach($this->brand->id);

        $this->getJson('/api/product/search?name=Branded')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.brands.0.id', $this->brand->id);
    }
}
