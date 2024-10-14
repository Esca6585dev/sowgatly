<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_list_categories()
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name_tm', 'name_en', 'name_ru', 'category_id', 'image_url']
                     ]
                 ]);
    }

    /** @test */
    public function it_can_list_parent_categories()
    {
        Category::factory()->count(3)->create(['category_id' => null]);
        Category::factory()->count(2)->create(['category_id' => 1]);

        $response = $this->getJson('/api/categories?parent=true');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_get_subcategories()
    {
        $parentCategory = Category::factory()->create();
        Category::factory()->count(3)->create(['category_id' => $parentCategory->id]);

        $response = $this->getJson("/api/categories/{$parentCategory->id}/subcategories");

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_create_a_category()
    {
        Storage::fake('public');

        $data = [
            'name_tm' => $this->faker->word,
            'name_en' => $this->faker->word,
            'name_ru' => $this->faker->word,
            'image' => UploadedFile::fake()->image('category.jpg'),
            'category_id' => null,
        ];

        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Category created successfully',
                 ]);

        $this->assertDatabaseHas('categories', [
            'name_tm' => $data['name_tm'],
            'name_en' => $data['name_en'],
            'name_ru' => $data['name_ru'],
        ]);

        $category = Category::where('name_en', $data['name_en'])->first();
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image);
    }

    /** @test */
    public function it_can_show_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['id', 'name_tm', 'name_en', 'name_ru', 'category_id', 'image_url']
                 ]);
    }

    /** @test */
    public function it_can_update_a_category()
    {
        $category = Category::factory()->create();

        Storage::fake('public');

        $data = [
            'name_tm' => $this->faker->word,
            'name_en' => $this->faker->word,
            'name_ru' => $this->faker->word,
            'image' => UploadedFile::fake()->image('new_category.jpg'),
            'category_id' => null,
            '_method' => 'PUT',
        ];

        $response = $this->postJson("/api/categories/{$category->id}", $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Category updated successfully',
                 ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name_tm' => $data['name_tm'],
            'name_en' => $data['name_en'],
            'name_ru' => $data['name_ru'],
        ]);

        $updatedCategory = Category::find($category->id);
        $this->assertNotNull($updatedCategory->image);
        Storage::disk('public')->assertExists($updatedCategory->image);
    }

    /** @test */
    public function it_can_delete_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}