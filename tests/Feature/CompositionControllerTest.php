<?php

namespace Tests\Feature;

use App\Models\Composition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompositionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_index_returns_all_compositions()
    {
        $compositions = Composition::factory()->count(3)->create();

        $response = $this->getJson('/api/compositions');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at']
                ]
            ]);
    }

    public function test_store_creates_new_composition()
    {
        $compositionData = ['name' => $this->faker->word];

        $response = $this->postJson('/api/compositions', $compositionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'created_at', 'updated_at']
            ]);

        $this->assertDatabaseHas('compositions', $compositionData);
    }

    public function test_show_returns_specific_composition()
    {
        $composition = Composition::factory()->create();

        $response = $this->getJson("/api/compositions/{$composition->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $composition->id,
                    'name' => $composition->name
                ]
            ]);
    }

    public function test_update_modifies_existing_composition()
    {
        $composition = Composition::factory()->create();
        $updatedData = ['name' => $this->faker->word];

        $response = $this->putJson("/api/compositions/{$composition->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $composition->id,
                    'name' => $updatedData['name']
                ]
            ]);

        $this->assertDatabaseHas('compositions', $updatedData);
    }

    public function test_destroy_deletes_composition()
    {
        $composition = Composition::factory()->create();

        $response = $this->deleteJson("/api/compositions/{$composition->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('compositions', ['id' => $composition->id]);
    }

    public function test_store_validates_input()
    {
        $response = $this->postJson('/api/compositions', ['name' => '']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_validates_input()
    {
        $composition = Composition::factory()->create();

        $response = $this->putJson("/api/compositions/{$composition->id}", ['name' => '']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_show_returns_404_for_non_existent_composition()
    {
        $response = $this->getJson("/api/compositions/9999");

        $response->assertStatus(404);
    }
}