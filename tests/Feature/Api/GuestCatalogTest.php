<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Region;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * With APP_API_GUEST_BROWSING on (the default) the read-only catalog is public.
 * Extends the plain TestCase so no user is signed in.
 */
class GuestCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function catalog(): array
    {
        $city = Region::factory()->city()->create();
        $shop = Shop::factory()->create(['region_id' => $city->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['shop_id' => $shop->id, 'category_id' => $category->id]);
        ProductReview::factory()->create(['product_id' => $product->id]);

        return [$city, $shop, $category, $product];
    }

    public function test_guests_can_read_the_catalog(): void
    {
        [$city, $shop, $category, $product] = $this->catalog();

        $this->getJson('/api/product/search?region_id=' . $city->id)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/product/category/' . $category->id)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/products/' . $product->id)->assertOk()->assertJsonPath('data.id', $product->id);
        $this->getJson('/api/products/' . $product->id . '/reviews')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.can_review', false);
        $this->getJson('/api/categories')->assertOk();
        $this->getJson('/api/categories/' . $category->id)->assertOk();
        $this->getJson('/api/categories/' . $category->id . '/subcategories')->assertOk();
        $this->getJson('/api/shops/' . $shop->id)->assertOk()->assertJsonPath('data.id', $shop->id);
        $this->getJson('/api/regions')->assertOk();
        $this->getJson('/api/regions/' . $city->id)->assertOk();
        $this->getJson('/api/regions/parent/' . $city->parent_id)->assertOk();
        $this->getJson('/api/brands')->assertOk();
        $this->getJson('/api/compositions')->assertOk();
    }

    public function test_guests_still_need_a_token_for_everything_personal(): void
    {
        [, $shop, , $product] = $this->catalog();

        $this->getJson('/api/products')->assertStatus(401); // own-shop listing
        $this->getJson('/api/shops')->assertStatus(401);    // own-shop listing
        $this->postJson('/api/products/' . $product->id . '/reviews', ['rating' => 5])->assertStatus(401);
        $this->getJson('/api/cart')->assertStatus(401);
        $this->getJson('/api/favorites')->assertStatus(401);
        $this->getJson('/api/orders')->assertStatus(401);
        $this->getJson('/api/users/me')->assertStatus(401);
        $this->postJson('/api/products', [])->assertStatus(401);
        $this->putJson('/api/shops/' . $shop->id, ['name' => 'x'])->assertStatus(401);
    }

    public function test_a_bearer_token_on_a_public_route_still_identifies_the_user(): void
    {
        [, , , $product] = $this->catalog();
        $user = User::factory()->create();
        $token = $user->createToken('t')->plainTextToken;

        // Same payload as a guest, and the token is accepted rather than rejected.
        $this->withToken($token)->getJson('/api/products/' . $product->id)
            ->assertOk()
            ->assertJsonPath('data.id', $product->id);

        // A garbage token on a public route is ignored, not a 401.
        $this->withToken('not-a-token')->getJson('/api/products/' . $product->id)->assertOk();
    }

    public function test_the_flag_can_turn_guest_browsing_off(): void
    {
        putenv('APP_API_GUEST_BROWSING=false');
        try {
            $this->refreshApplication();
            $this->getJson('/api/categories')->assertStatus(401);
            $this->getJson('/api/regions')->assertStatus(401);
        } finally {
            putenv('APP_API_GUEST_BROWSING');
        }
    }
}
