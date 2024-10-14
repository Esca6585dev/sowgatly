<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\Shop;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $category;
    protected $shop;
    protected $brand;

    public function setUp(): void
    {
        parent::setUp();

        // Create necessary related models
        $this->category = Category::factory()->create();
        $this->shop = Shop::factory()->create();
        $this->brand = Brand::factory()->create();
    }

    /** @test */
    public function it_can_list_products()
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_a_product()
    {
        $productData = [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'discount' => $this->faker->numberBetween(0, 50),
            'category_id' => $this->category->id,
            'shop_id' => $this->shop->id,
            'brand_ids' => [$this->brand->id],
            'status' => true,
            'gender' => 'Men',
            'sizes' => ['S', 'M', 'L'],
            'separated_sizes' => ['XS', 'XL'],
            'color' => $this->faker->colorName,
            'manufacturer' => $this->faker->company,
            'width' => $this->faker->randomFloat(2, 1, 100),
            'height' => $this->faker->randomFloat(2, 1, 100),
            'weight' => $this->faker->randomFloat(2, 0.1, 10),
            'production_time' => $this->faker->numberBetween(1, 30),
            'min_order' => $this->faker->numberBetween(1, 10),
            'seller_status' => true,
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'discount',
                    'category_id',
                    'shop_id',
                    'status',
                    'gender',
                    'sizes',
                    'separated_sizes',
                    'color',
                    'manufacturer',
                    'width',
                    'height',
                    'weight',
                    'production_time',
                    'min_order',
                    'seller_status',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => $productData['name'],
            'description' => $productData['description'],
        ]);
    }

    /** @test */
    public function it_can_show_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                ],
            ]);
    }

    /** @test */
    public function it_returns_product_not_found_for_invalid_id()
    {
        $response = $this->getJson("/api/products/999");

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Product not found',
            ]);
    }

    /** @test */
    public function it_can_update_a_product()
    {
        $product = Product::factory()->create();

        $updatedData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated product description',
            'price' => 99.99,
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => [
                    'id' => $product->id,
                    'name' => 'Updated Product Name',
                    'description' => 'Updated product description',
                    'price' => 99.99,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'description' => 'Updated product description',
            'price' => 99.99,
        ]);
    }

    /** @test */
    public function it_can_delete_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_a_product()
    {
        $response = $this->postJson('/api/products', []);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Validation error',
            ])
            ->assertJsonValidationErrors(['name', 'description', 'price', 'category_id', 'shop_id', 'status', 'seller_status']);
    }

    /** @test */
    public function it_validates_numeric_fields_when_creating_a_product()
    {
        $invalidData = [
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 'not a number',
            'discount' => 'not a number',
            'category_id' => $this->category->id,
            'shop_id' => $this->shop->id,
            'status' => true,
            'seller_status' => true,
        ];

        $response = $this->postJson('/api/products', $invalidData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Validation error',
            ])
            ->assertJsonValidationErrors(['price', 'discount']);
    }
}