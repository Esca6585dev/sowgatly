<?php

namespace Tests\Feature\Admin;

use App\Http\Resources\CategoryResource;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryAdminTest extends AdminTestCase
{
    private function names(string $tm, ?int $parentId = null): array
    {
        return ['name_tm' => $tm, 'name_en' => $tm . ' en', 'name_ru' => $tm . ' ru', 'image' => null, 'category_id' => $parentId];
    }

    /** @test */
    public function the_three_lists_filter_by_type_and_search_works()
    {
        $parent = Category::create($this->names('Güller'));
        Category::create($this->names('Gyzyl güller', $parent->id));

        $this->get($this->adminUrl('all/category'))->assertOk()->assertSee('Güller')->assertSee('Gyzyl güller');
        $this->get($this->adminUrl('parent/category'))->assertOk()->assertSee('>Güller</a>', false)->assertDontSee('Gyzyl güller');
        $this->get($this->adminUrl('sub/category'))->assertOk()->assertSee('Gyzyl güller')->assertDontSee("sub/category/{$parent->id}\"", false);
        $this->get($this->adminUrl('other/category'))->assertNotFound();

        // AJAX search returns only the table partial and keeps the type filter.
        $this->get($this->adminUrl('parent/category?search=gyzyl'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertDontSee('<html', false)->assertDontSee('Gyzyl güller');
        $this->get($this->adminUrl('all/category?search=gyzyl'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertDontSee('<html', false)->assertSee('Gyzyl güller');
        // Searches the other languages too.
        $this->get($this->adminUrl('all/category?search=Gyzyl güller ru'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Gyzyl güller');
    }

    /** @test */
    public function the_parent_select_is_shown_for_subcategories_and_all_only()
    {
        Category::create($this->names('Güller'));

        $this->get($this->adminUrl('parent/category/create'))->assertOk()->assertSee('name="name_tm"', false)->assertDontSee('name="category_id"', false);
        $this->get($this->adminUrl('sub/category/create'))->assertOk()->assertSee('name="category_id"', false)->assertSee('Güller');
        $this->get($this->adminUrl('all/category/create'))->assertOk()->assertSee('name="category_id"', false);
    }

    /** @test */
    public function a_parent_category_is_stored_with_an_image_the_api_can_serve()
    {
        Storage::fake('public');

        $this->post($this->adminUrl('parent/category'), [
            'name_tm' => 'Tortlar', 'name_en' => 'Cakes', 'name_ru' => 'Торты',
            'image' => UploadedFile::fake()->image('cake.png', 200, 200),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $category = Category::where('name_tm', 'Tortlar')->firstOrFail();
        $this->assertNull($category->category_id);
        $this->assertStringStartsWith('storage/categories/', $category->image);
        Storage::disk('public')->assertExists(substr($category->image, strlen('storage/')));
        $this->assertSame(asset($category->image), (new CategoryResource($category))->resolve()['image']);

        $this->get($this->adminUrl("parent/category/{$category->id}"))->assertOk()->assertSee('Торты')->assertSee(asset($category->image));
    }

    /** @test */
    public function a_subcategory_needs_a_parent_category()
    {
        $parent = Category::create($this->names('Güller'));
        $sub = Category::create($this->names('Gyzyl', $parent->id));

        $this->from($this->adminUrl('sub/category/create'))->post($this->adminUrl('sub/category'), ['name_tm' => '', 'name_en' => 'X', 'name_ru' => 'X'])
            ->assertRedirect($this->adminUrl('sub/category/create'))
            ->assertSessionHasErrors(['name_tm', 'category_id']);
        // Only two levels: a subcategory cannot be a parent.
        $this->post($this->adminUrl('sub/category'), $this->names('Ak', $sub->id))->assertSessionHasErrors('category_id');

        $this->post($this->adminUrl('sub/category'), array_filter($this->names('Ak', $parent->id)))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals($parent->id, Category::where('name_tm', 'Ak')->value('category_id'));
    }

    /** @test */
    public function show_edit_update_and_delete()
    {
        $parent = Category::create($this->names('Güller'));
        $sub = Category::create($this->names('Gyzyl güller', $parent->id));
        Product::factory()->create(['category_id' => $sub->id, 'name_tm' => 'Bägül desesi']);

        $this->get($this->adminUrl("parent/category/{$parent->id}"))->assertOk()->assertSee('Gyzyl güller')->assertSee('Bägül desesi');
        $this->get($this->adminUrl("sub/category/{$sub->id}"))->assertOk()->assertSee('Güller')->assertSee('Bägül desesi');
        $this->get($this->adminUrl("all/category/{$sub->id}/edit"))->assertOk()->assertSee('Gyzyl güller')->assertSee('name="category_id"', false);
        $this->get($this->adminUrl("parent/category/{$parent->id}/edit"))->assertOk()->assertDontSee('name="category_id"', false);

        $this->put($this->adminUrl("sub/category/{$sub->id}"), array_filter($this->names('Gyzyl bägüller', $parent->id)))
            ->assertRedirect($this->adminUrl("sub/category/{$sub->id}"));
        $this->assertSame('Gyzyl bägüller', $sub->fresh()->name_tm);

        // Editing a parent from the "parent" list keeps it a parent.
        $this->put($this->adminUrl("parent/category/{$parent->id}"), array_filter($this->names('Gül')))->assertSessionHasNoErrors();
        $this->assertNull($parent->fresh()->category_id);
        // A category cannot be its own parent, and a parent with subcategories cannot become one.
        $other = Category::create($this->names('Sowgatlar'));
        $this->put($this->adminUrl("all/category/{$parent->id}"), array_filter($this->names('Gül', $parent->id)))->assertSessionHasErrors('category_id');
        $this->put($this->adminUrl("all/category/{$parent->id}"), array_filter($this->names('Gül', $other->id)))->assertSessionHasErrors('category_id');

        // A category with products is not deleted.
        $this->delete($this->adminUrl("parent/category/{$parent->id}"))->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull($parent->fresh());

        Product::query()->delete();
        Attribute::create(['type' => 'Reňk', 'value' => 'Gyzyl', 'category_id' => $sub->id]);
        $this->delete($this->adminUrl("parent/category/{$parent->id}"))->assertRedirect($this->adminUrl('parent/category'));
        $this->assertNull($parent->fresh());
        $this->assertNull($sub->fresh());
        $this->assertDatabaseCount('attributes', 0);
    }
}
