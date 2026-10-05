<?php

namespace Tests\Feature\Admin;

use App\Models\Attribute;
use App\Models\Category;

class AttributeAdminTest extends AdminTestCase
{
    private function category(string $tm, ?int $parentId = null): Category
    {
        return Category::create(['name_tm' => $tm, 'name_en' => $tm, 'name_ru' => $tm, 'category_id' => $parentId]);
    }

    /** @test */
    public function list_search_and_category_filter()
    {
        $flowers = $this->category('Güller');
        $cakes = $this->category('Tortlar');
        Attribute::create(['type' => 'Reňk', 'value' => 'Gyzyl', 'category_id' => $flowers->id]);
        Attribute::create(['type' => 'Ölçeg', 'value' => '20 sm', 'category_id' => $cakes->id]);

        $this->get($this->adminUrl('attribute'))->assertOk()->assertSee('Reňk')->assertSee('Ölçeg')->assertSee('Güller');
        $this->get($this->adminUrl("attribute?category_id={$cakes->id}"))->assertOk()->assertSee('20 sm')->assertDontSee('Gyzyl');
        $this->get($this->adminUrl('attribute?search=gyz'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Gyzyl')->assertDontSee('20 sm')->assertDontSee('<html', false);
    }

    /** @test */
    public function create_show_edit_update_and_delete()
    {
        $parent = $this->category('Güller');
        $sub = $this->category('Bägüller', $parent->id);

        $this->get($this->adminUrl('attribute/create'))->assertOk()->assertSee('name="type"', false)->assertSee('Güller › Bägüller');
        $this->post($this->adminUrl('attribute'), ['type' => 'Reňk', 'value' => 'Ak', 'category_id' => $sub->id])->assertRedirect();
        $attribute = Attribute::where('value', 'Ak')->firstOrFail();
        $this->assertEquals($sub->id, $attribute->category_id);

        Attribute::create(['type' => 'Reňk', 'value' => 'Sary']);
        $this->get($this->adminUrl("attribute/{$attribute->id}"))->assertOk()->assertSee('Bägüller')->assertSee('Sary');
        $this->get($this->adminUrl("attribute/{$attribute->id}/edit"))->assertOk()->assertSee('value="Ak"', false);

        $this->put($this->adminUrl("attribute/{$attribute->id}"), ['type' => 'Reňk', 'value' => 'Gök', 'category_id' => ''])
            ->assertRedirect($this->adminUrl("attribute/{$attribute->id}"));
        $attribute->refresh();
        $this->assertSame('Gök', $attribute->value);
        $this->assertNull($attribute->category_id);

        $this->delete($this->adminUrl("attribute/{$attribute->id}"))->assertRedirect($this->adminUrl('attribute'));
        $this->assertNull($attribute->fresh());
    }

    /** @test */
    public function validation_errors()
    {
        $this->from($this->adminUrl('attribute/create'))->post($this->adminUrl('attribute'), ['type' => '', 'value' => '', 'category_id' => 999])
            ->assertRedirect($this->adminUrl('attribute/create'))
            ->assertSessionHasErrors(['type', 'value', 'category_id']);
    }
}
