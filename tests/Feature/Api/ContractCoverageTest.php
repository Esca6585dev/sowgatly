<?php

namespace Tests\Feature\Api;

use App\Models\Address;
use App\Models\Brand;
use App\Models\ChatThread;
use App\Models\Composition;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Happy paths and failure cases for routes no other feature test reached,
 * so every /api route has a 2xx contract fixture and every write route a
 * 4xx one (see tests/Contract/RecordsContract.php).
 */
class ContractCoverageTest extends ApiTestCase
{
    /** An id no table reaches, also on MySQL where ids are not reset between tests. */
    private const MISSING_ID = 999999;

    private function ownShop(array $attributes = []): Shop
    {
        return Shop::factory()->create(array_merge(['user_id' => $this->user->id, 'region_id' => $this->cityRegion()->id], $attributes));
    }

    private function orderFor(User $customer, Shop $shop, string $status = 'pending'): Order
    {
        $product = Product::factory()->create(['shop_id' => $shop->id, 'price' => 100, 'discount' => 0]);
        $order = Order::create([
            'user_id' => $customer->id,
            'shop_id' => $shop->id,
            'total_amount' => 120,
            'items_total' => 100,
            'delivery_fee' => 20,
            'status' => $status,
            'delivery_type' => 'asap',
            'recipient_phone' => '65656585',
            'delivery_address' => 'Aýtakow köç. 17',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 100]);

        return $order;
    }

    private function threadWithMessage(User $customer, Shop $shop, string $body = 'Salam'): ChatThread
    {
        $thread = ChatThread::create(['user_id' => $customer->id, 'shop_id' => $shop->id]);
        $thread->post('user', $customer->id, $body);

        return $thread->fresh();
    }

    // --- Read-only catalog -------------------------------------------------

    public function test_shop_addresses_can_be_listed_and_shown(): void
    {
        $shop = Shop::factory()->create();
        $address = Address::factory()->create(['shop_id' => $shop->id, 'address_name' => 'Aşgabat, Parahat 7/12', 'postal_code' => '744000']);

        $this->getJson('/api/addresses')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $address->id);

        $this->getJson("/api/addresses/{$address->id}")
            ->assertOk()
            ->assertJsonPath('data.address_name', 'Aşgabat, Parahat 7/12')
            ->assertJsonPath('data.shop_id', $shop->id);
    }

    public function test_a_brand_can_be_shown(): void
    {
        $brand = Brand::factory()->create();

        $this->getJson("/api/brands/{$brand->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $brand->id)
            ->assertJsonPath('data.name', $brand->name);
    }

    public function test_a_composition_can_be_shown(): void
    {
        $composition = Composition::factory()->create();

        $this->getJson("/api/compositions/{$composition->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $composition->id)
            ->assertJsonPath('data.name', $composition->name);
    }

    // --- Shop owner: products ----------------------------------------------

    public function test_a_shop_owner_lists_their_own_products(): void
    {
        $shop = $this->ownShop();
        $mine = Product::factory()->create(['shop_id' => $shop->id]);
        Product::factory()->create(); // another shop's product

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id);
    }

    public function test_a_shop_owner_can_patch_their_product(): void
    {
        $shop = $this->ownShop();
        $product = Product::factory()->create(['shop_id' => $shop->id]);

        $this->patchJson("/api/products/{$product->id}", ['name_en' => 'Patched name', 'stock' => 7])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Product updated successfully'])
            ->assertJsonPath('data.name.en', 'Patched name');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name_en' => 'Patched name', 'stock' => 7]);
    }

    public function test_patching_another_shops_product_is_forbidden(): void
    {
        $this->ownShop();
        $product = Product::factory()->create();

        $this->patchJson("/api/products/{$product->id}", ['name_en' => 'Hijacked'])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'You can only edit products of your own shop']);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name_en' => $product->name_en]);
    }

    // --- Shop owner: shop --------------------------------------------------

    public function test_a_shop_owner_can_patch_their_shop(): void
    {
        $shop = $this->ownShop();

        $this->patchJson("/api/shops/{$shop->id}", ['name' => 'Täze at', 'phone' => '65000000'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Täze at')
            ->assertJsonPath('data.phone', '65000000');

        $this->assertDatabaseHas('shops', ['id' => $shop->id, 'name' => 'Täze at']);
    }

    public function test_patching_another_users_shop_is_forbidden(): void
    {
        $shop = Shop::factory()->create();

        $this->patchJson("/api/shops/{$shop->id}", ['name' => 'Hijacked'])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'You do not have permission to update this shop']);
    }

    public function test_a_shop_owner_can_delete_their_shop(): void
    {
        Storage::fake('public');
        // No image: the replay server must not delete a real seeder file.
        $shop = $this->ownShop(['image' => null]);

        $this->deleteJson("/api/shops/{$shop->id}")
            ->assertOk()
            ->assertJson(['status' => true, 'message' => 'Shop deleted successfully']);

        $this->assertDatabaseMissing('shops', ['id' => $shop->id]);
    }

    public function test_deleting_another_users_shop_is_forbidden(): void
    {
        $shop = Shop::factory()->create(['image' => null]);

        $this->deleteJson("/api/shops/{$shop->id}")
            ->assertStatus(403)
            ->assertJson(['status' => false, 'message' => 'You can only delete your own shop']);

        $this->assertDatabaseHas('shops', ['id' => $shop->id]);
    }

    // --- Orders --------------------------------------------------------------

    public function test_user_orders_alias_lists_the_callers_orders(): void
    {
        $shop = Shop::factory()->create();
        $order = $this->orderFor($this->user, $shop);
        $this->orderFor(User::factory()->create(), $shop); // someone else's

        $this->getJson('/api/user/orders')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $order->id)
            ->assertJsonPath('0.number', $order->number);
    }

    public function test_a_shop_owner_lists_their_shop_orders(): void
    {
        $shop = $this->ownShop();
        $customer = User::factory()->create();
        $order = $this->orderFor($customer, $shop, 'processing');
        $this->orderFor($customer, Shop::factory()->create()); // another shop's

        $this->getJson('/api/shop/orders')
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order->id)
            ->assertJsonPath('data.0.user.id', $customer->id);
    }

    // --- Chats ---------------------------------------------------------------

    public function test_a_customer_lists_their_chat_threads(): void
    {
        $shop = Shop::factory()->create();
        $thread = $this->threadWithMessage($this->user, $shop, 'Gül barmy?');
        $this->threadWithMessage(User::factory()->create(), $shop); // someone else's

        $this->getJson('/api/me/chats')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $thread->id)
            ->assertJsonPath('data.0.shop.id', $shop->id)
            ->assertJsonPath('data.0.last_message.body', 'Gül barmy?')
            ->assertJsonPath('data.0.unread', 0);
    }

    public function test_marking_an_unknown_chat_read_answers_404(): void
    {
        $this->postJson('/api/me/chats/' . self::MISSING_ID . '/read')
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Chat not found']);
    }

    public function test_a_shop_owner_reads_a_thread_and_send_validates_the_body(): void
    {
        $shop = $this->ownShop();
        $customer = User::factory()->create();
        $thread = $this->threadWithMessage($customer, $shop, 'Salam');

        $this->getJson("/api/shop/chats/{$thread->id}/messages")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.body', 'Salam')
            ->assertJsonPath('data.0.sender_type', 'user');

        $this->postJson("/api/shop/chats/{$thread->id}/messages", ['body' => ''])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('chat_messages', 1);
    }

    public function test_a_shop_cannot_mark_another_shops_thread_read(): void
    {
        $this->ownShop();
        $thread = $this->threadWithMessage(User::factory()->create(), Shop::factory()->create());

        $this->postJson("/api/shop/chats/{$thread->id}/read")
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Chat not found']);
    }

    // --- Customer account writes: failures ---------------------------------

    public function test_removing_an_unknown_cart_item_answers_404(): void
    {
        $this->deleteJson('/api/cart/items/' . self::MISSING_ID)
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Cart item not found']);
    }

    public function test_an_address_requires_the_address_text(): void
    {
        $this->postJson('/api/me/addresses', ['title' => 'Home'])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseCount('user_addresses', 0);
    }

    public function test_the_waitlist_requires_an_existing_product(): void
    {
        $this->postJson('/api/me/waitlist', ['product_id' => self::MISSING_ID])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_updating_the_profile_requires_a_name(): void
    {
        $this->postJson('/api/users/me', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'name' => $this->user->name]);
    }
}
