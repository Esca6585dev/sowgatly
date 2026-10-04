<?php

namespace Tests\Feature\Api;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Region;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** GET /api/home and GET /api/banners are public, so no user is signed in here. */
class HomeFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_returns_categories_banners_and_sections_for_the_city(): void
    {
        $ashgabat = Region::factory()->city()->create(['name' => 'Aşgabat']);
        $mary = Region::factory()->city()->create(['name' => 'Mary']);
        $flowers = Category::factory()->create(['name_tm' => 'Güller', 'name_ru' => 'Цветы', 'name_en' => 'Flowers']);
        $sub = Category::factory()->create(['category_id' => $flowers->id]);
        $gifts = Category::factory()->create();

        $shop = Shop::factory()->create(['region_id' => $ashgabat->id]);
        Product::factory()->count(2)->create(['shop_id' => $shop->id, 'category_id' => $flowers->id, 'production_time' => 60]);
        Product::factory()->create(['shop_id' => $shop->id, 'category_id' => $sub->id, 'production_time' => 600]);
        // Only one gift product: the category section needs at least two.
        Product::factory()->create(['shop_id' => $shop->id, 'category_id' => $gifts->id]);
        // Hidden products never show up.
        Product::factory()->inactive()->create(['shop_id' => $shop->id, 'category_id' => $flowers->id]);
        Product::factory()->create(['shop_id' => $shop->id, 'category_id' => $flowers->id, 'seller_status' => false]);
        // Another city.
        Product::factory()->count(3)->create(['shop_id' => Shop::factory()->create(['region_id' => $mary->id])->id, 'category_id' => $flowers->id]);

        Banner::factory()->create(['title_tm' => 'Global']);
        Banner::factory()->create(['title_tm' => 'Mary only', 'region_id' => $mary->id]);
        Banner::factory()->create(['title_tm' => 'Off', 'is_active' => false]);
        Banner::factory()->create(['title_tm' => 'Expired', 'ends_at' => now()->subDay()]);

        $response = $this->getJson('/api/home?region_id=' . $ashgabat->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('region.name', 'Aşgabat')
            ->assertJsonCount(2, 'categories')   // root categories only
            ->assertJsonCount(1, 'banners')
            ->assertJsonPath('banners.0.title.tm', 'Global');

        $sections = collect($response->json('sections'))->keyBy('key');

        $this->assertTrue($sections->has('delivery_today'));
        $this->assertCount(2, $sections['delivery_today']['products']);
        $this->assertSame('Доставка сегодня', $sections['delivery_today']['title']['ru']);

        $this->assertTrue($sections->has('popular'));

        $this->assertTrue($sections->has('category:' . $flowers->id));
        $this->assertSame($flowers->id, $sections['category:' . $flowers->id]['category_id']);
        $this->assertSame('Цветы', $sections['category:' . $flowers->id]['title']['ru']);
        // 2 flowers + 1 subcategory product, nothing hidden, nothing from Mary.
        $this->assertCount(3, $sections['category:' . $flowers->id]['products']);

        $this->assertFalse($sections->has('category:' . $gifts->id));

        $this->assertArrayHasKey('reviews_avg', $sections['popular']['products'][0]);
    }

    public function test_home_without_a_region_covers_every_city_and_per_section_is_capped(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(8)->create(['category_id' => $category->id, 'production_time' => 30]);

        $response = $this->getJson('/api/home?per_section=3')->assertOk()->assertJsonPath('region', null);

        $sections = collect($response->json('sections'))->keyBy('key');
        $this->assertCount(3, $sections['delivery_today']['products']);
        $this->assertCount(3, $sections['category:' . $category->id]['products']);
    }

    public function test_popular_section_orders_by_recent_sales(): void
    {
        $category = Category::factory()->create();
        $shop = Shop::factory()->create();
        [$quiet, $bestseller] = Product::factory()->count(2)->create(['shop_id' => $shop->id, 'category_id' => $category->id])->all();

        $order = Order::factory()->status('completed')->create(['shop_id' => $shop->id]);
        OrderItem::factory()->count(3)->create(['order_id' => $order->id, 'product_id' => $bestseller->id]);

        $sections = collect($this->getJson('/api/home')->json('sections'))->keyBy('key');

        $this->assertSame($bestseller->id, $sections['popular']['products'][0]['id']);
        $this->assertSame($quiet->id, $sections['popular']['products'][1]['id']);
    }

    public function test_home_cache_is_refreshed_when_banners_or_products_change(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(2)->create(['category_id' => $category->id]);

        $this->getJson('/api/home')->assertOk()->assertJsonCount(0, 'banners');

        Banner::factory()->create();
        $this->getJson('/api/home')->assertOk()->assertJsonCount(1, 'banners');

        Product::factory()->create(['category_id' => $category->id]);
        $sections = collect($this->getJson('/api/home')->json('sections'))->keyBy('key');
        $this->assertCount(3, $sections['category:' . $category->id]['products']);
    }

    public function test_banners_endpoint_filters_by_region(): void
    {
        $city = Region::factory()->city()->create();
        Banner::factory()->create(['title_tm' => 'Global', 'position' => 2]);
        Banner::factory()->create(['title_tm' => 'City', 'region_id' => $city->id, 'position' => 1]);
        Banner::factory()->create(['title_tm' => 'Future', 'starts_at' => now()->addDay()]);

        $this->getJson('/api/banners')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title.tm', 'Global');

        $this->getJson('/api/banners?region_id=' . $city->id)
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title.tm', 'City')
            ->assertJsonPath('data.1.title.tm', 'Global')
            ->assertJsonStructure(['data' => [['id', 'title' => ['tm', 'ru', 'en'], 'subtitle', 'image', 'link_type', 'link_value', 'position']]]);
    }

    public function test_rating_summaries_on_products_and_shops(): void
    {
        $shop = Shop::factory()->create();
        $rated = Product::factory()->create(['shop_id' => $shop->id]);
        $unrated = Product::factory()->create(['shop_id' => $shop->id]);
        ProductReview::factory()->create(['product_id' => $rated->id, 'rating' => 5]);
        ProductReview::factory()->create(['product_id' => $rated->id, 'rating' => 4]);

        $this->getJson('/api/products/' . $rated->id)->assertOk()
            ->assertJsonPath('data.reviews_avg', 4.5)
            ->assertJsonPath('data.reviews_count', 2);
        $this->getJson('/api/products/' . $unrated->id)->assertOk()
            ->assertJsonPath('data.reviews_avg', null)
            ->assertJsonPath('data.reviews_count', 0);

        $this->getJson('/api/shops/' . $shop->id)->assertOk()
            ->assertJsonPath('data.rating_avg', 4.5)
            ->assertJsonPath('data.reviews_count', 2);

        $list = $this->getJson('/api/product/search?shop_id=' . $shop->id)->assertOk()->json('data');
        $byId = collect($list)->keyBy('id');
        $this->assertSame(4.5, $byId[$rated->id]['reviews_avg']);
        $this->assertSame(0, $byId[$unrated->id]['reviews_count']);
    }

    public function test_search_filters_min_rating_delivery_today_and_popular_sort(): void
    {
        $shop = Shop::factory()->create();
        $fast = Product::factory()->create(['shop_id' => $shop->id, 'production_time' => 120]);
        $slow = Product::factory()->create(['shop_id' => $shop->id, 'production_time' => 1440]);
        ProductReview::factory()->create(['product_id' => $fast->id, 'rating' => 3]);
        ProductReview::factory()->create(['product_id' => $slow->id, 'rating' => 5]);

        $ids = fn ($url) => collect($this->getJson($url)->assertOk()->json('data'))->pluck('id')->all();

        $this->assertSame([$fast->id], $ids('/api/product/search?delivery_today=1'));
        $this->assertSame([$slow->id], $ids('/api/product/search?min_rating=4'));
        $this->assertEqualsCanonicalizing([$fast->id, $slow->id], $ids('/api/product/search?min_rating=3'));
        $this->assertSame(2, $this->getJson('/api/product/search?min_rating=3')->json('meta.total'));

        $order = Order::factory()->create(['shop_id' => $shop->id]);
        OrderItem::factory()->count(2)->create(['order_id' => $order->id, 'product_id' => $fast->id]);
        $this->assertSame([$fast->id, $slow->id], $ids('/api/product/search?sort=popular'));
    }
}
