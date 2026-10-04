<?php

namespace Tests\Feature\Api;

use App\Models\FavoriteCollection;
use App\Models\User;

class FavoriteCollectionTest extends ApiTestCase
{
    public function test_collections_crud_with_products_and_covers(): void
    {
        $shop = $this->shopWithProducts(3);
        [$a, $b, $c] = $this->productsOf($shop)->all();

        $this->getJson('/api/me/collections')->assertOk()->assertJsonCount(0, 'data');

        $id = $this->postJson('/api/me/collections', ['name' => 'Что я хочу'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Что я хочу')
            ->assertJsonPath('data.items_count', 0)
            ->json('data.id');

        $this->postJson("/api/me/collections/{$id}/products", ['product_id' => $a->id])->assertStatus(201)->assertJson(['added' => true]);
        $this->postJson("/api/me/collections/{$id}/products", ['product_id' => $a->id])->assertOk()->assertJson(['added' => false]);
        $this->postJson("/api/me/collections/{$id}/products", ['product_id' => $b->id])->assertStatus(201);

        $list = $this->getJson('/api/me/collections')->assertOk()->assertJsonCount(1, 'data')->json('data.0');
        $this->assertSame(2, $list['items_count']);
        $this->assertCount(2, $list['covers']);

        $show = $this->getJson("/api/me/collections/{$id}")->assertOk()
            ->assertJsonPath('data.items_count', 2)
            ->assertJsonPath('meta.total', 2)
            ->json('data.products');
        $this->assertEqualsCanonicalizing([$a->id, $b->id], array_column($show, 'id'));
        $this->assertArrayHasKey('reviews_avg', $show[0]);

        $this->putJson("/api/me/collections/{$id}", ['name' => 'Täze at', 'position' => 3])
            ->assertOk()
            ->assertJsonPath('data.name', 'Täze at')
            ->assertJsonPath('data.position', 3);

        $this->deleteJson("/api/me/collections/{$id}/products/{$a->id}")->assertOk();
        $this->deleteJson("/api/me/collections/{$id}/products/{$c->id}")->assertStatus(404);
        $this->assertSame(1, $this->getJson("/api/me/collections/{$id}")->json('data.items_count'));

        $this->deleteJson("/api/me/collections/{$id}")->assertOk();
        $this->assertDatabaseCount('favorite_collections', 0);
        $this->assertDatabaseCount('favorite_collection_items', 0);
        $this->assertDatabaseCount('products', 3);
    }

    public function test_names_are_unique_per_user_and_required(): void
    {
        $this->postJson('/api/me/collections', ['name' => 'Gift'])->assertStatus(201);
        $this->postJson('/api/me/collections', ['name' => 'Gift'])->assertStatus(422);
        $this->postJson('/api/me/collections', ['name' => ''])->assertStatus(422);

        // The same name is fine for someone else.
        $this->actingAsCustomer(User::factory()->create());
        $this->postJson('/api/me/collections', ['name' => 'Gift'])->assertStatus(201);
    }

    public function test_other_users_collections_are_invisible(): void
    {
        $foreign = FavoriteCollection::factory()->create();
        $product = $this->productsOf($this->shopWithProducts(1))->first();

        $this->getJson("/api/me/collections/{$foreign->id}")->assertStatus(404);
        $this->putJson("/api/me/collections/{$foreign->id}", ['name' => 'x'])->assertStatus(404);
        $this->deleteJson("/api/me/collections/{$foreign->id}")->assertStatus(404);
        $this->postJson("/api/me/collections/{$foreign->id}/products", ['product_id' => $product->id])->assertStatus(404);
        $this->getJson('/api/me/collections')->assertJsonCount(0, 'data');
    }

    public function test_the_collection_limit_is_enforced(): void
    {
        FavoriteCollection::factory()->count(FavoriteCollection::MAX_PER_USER)->create(['user_id' => $this->user->id]);

        $this->postJson('/api/me/collections', ['name' => 'One more'])->assertStatus(422);
    }
}
