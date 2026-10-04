<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * The admin panel product form writes the same per-language columns as the API.
 */
class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected Shop $shop;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('secret'),
        ]);
        $this->shop = Shop::factory()->create();
        $this->category = Category::factory()->create();
    }

    private function formData(array $overrides = []): array
    {
        return array_merge([
            'name_tm' => 'Bägül',
            'name_en' => 'Rose',
            'name_ru' => 'Роза',
            'description_tm' => 'Gyzyl',
            'description_en' => 'Red',
            'description_ru' => 'Красная',
            'price' => '120.50',
            'discount' => '5',
            'stock' => '10',
            'production_time' => '60',
            'min_order' => '1',
            'shop_id' => $this->shop->id,
            'category_id' => $this->category->id,
            'status' => '1',
            'seller_status' => '1',
        ], $overrides);
    }

    public function test_admin_can_create_a_product_with_images()
    {
        $response = $this->actingAs($this->admin, 'admin')->post('/tm/admin/product', $this->formData([
            'images' => [UploadedFile::fake()->image('rose.jpg')],
        ]));

        $response->assertRedirect('/tm/admin/product');

        $this->assertDatabaseHas('products', [
            'name_tm' => 'Bägül',
            'name_en' => 'Rose',
            'price' => 120.50,
            'shop_id' => $this->shop->id,
            'category_id' => $this->category->id,
        ]);

        $product = Product::first();
        $this->assertCount(1, $product->images);
        $this->assertFileExists(public_path($product->images->first()->url));
        \File::deleteDirectory(public_path(dirname($product->images->first()->url)));
    }

    public function test_admin_product_form_validates_required_fields()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->from('/tm/admin/product/create')
            ->post('/tm/admin/product', []);

        $response->assertRedirect('/tm/admin/product/create')
            ->assertSessionHasErrors(['name_tm', 'name_en', 'name_ru', 'description_tm', 'price', 'shop_id', 'category_id']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_admin_can_update_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')->put("/tm/admin/product/{$product->id}", $this->formData([
            'name_en' => 'Updated rose',
            'discount' => '',
            'status' => '0',
        ]));

        $response->assertRedirect('/tm/admin/product');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name_en' => 'Updated rose',
            'discount' => null,
            'status' => 0,
        ]);
    }

    public function test_guests_cannot_create_products()
    {
        // Web guests get the legacy "Unauthenticated" body (see Handler), never the product.
        $response = $this->post('/tm/admin/product', $this->formData());

        $this->assertContains($response->status(), [200, 302, 401]);
        $this->assertDatabaseCount('products', 0);
    }
}
