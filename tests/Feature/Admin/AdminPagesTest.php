<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\ChatThread;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests: the new admin pages render and the status forms work.
 */
class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create([
            'first_name' => 'Admin', 'last_name' => 'Test', 'username' => 'admin-test',
            'email' => 'admin@example.com', 'password' => bcrypt('secret'),
        ]);
        $this->actingAs($this->admin, 'admin');
    }

    private function order(string $status = 'pending'): Order
    {
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        $product = Product::factory()->create(['shop_id' => $shop->id]);
        $order = Order::create([
            'user_id' => $user->id, 'shop_id' => $shop->id, 'total_amount' => 120, 'items_total' => 100,
            'delivery_fee' => 20, 'status' => $status, 'delivery_type' => 'asap', 'recipient_phone' => '65656585',
            'delivery_address' => 'x', 'payment_method' => 'online', 'payment_bank' => 'rysgal',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 100]);

        return $order;
    }

    /** @test */
    public function orders_pages_render_and_status_can_be_changed()
    {
        $order = $this->order();

        $this->get('/tm/admin/order')->assertOk()->assertSee($order->number);
        $this->get('/tm/admin/order?status=pending&payment_status=unpaid&search=' . $order->id)->assertOk()->assertSee($order->number);
        $this->get("/tm/admin/order/{$order->id}")->assertOk()->assertSee('Rysgal');

        $this->put("/tm/admin/order/{$order->id}", ['status' => 'processing', 'payment_status' => 'paid'])->assertRedirect();
        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);

        // Impossible transition is refused and nothing changes.
        $this->put("/tm/admin/order/{$order->id}", ['status' => 'pending'])->assertRedirect();
        $this->assertEquals('processing', $order->fresh()->status);
    }

    /** @test */
    public function chat_pages_render()
    {
        $thread = ChatThread::create(['user_id' => User::factory()->create()->id, 'shop_id' => Shop::factory()->create()->id]);
        $thread->post('user', $thread->user_id, 'Salam');

        $this->get('/tm/admin/chat')->assertOk()->assertSee('Salam');
        $this->get("/tm/admin/chat/{$thread->id}")->assertOk()->assertSee('Salam');
    }

    /** @test */
    public function shop_application_pages_render_and_decision_notifies_the_user()
    {
        $user = User::factory()->create();
        $application = ShopApplication::create(['user_id' => $user->id, 'name' => 'Gül', 'phone' => '65656585']);

        $this->get('/tm/admin/shop-application')->assertOk()->assertSee('Gül');
        $this->get("/tm/admin/shop-application/{$application->id}")->assertOk();

        $this->put("/tm/admin/shop-application/{$application->id}", ['status' => 'approved', 'admin_note' => 'ok'])->assertRedirect();
        $this->assertEquals('approved', $application->fresh()->status);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $user->id, 'type' => 'shop_application']);
    }

    /** @test */
    public function shop_form_renders_with_the_new_fields()
    {
        $shop = Shop::factory()->create();

        $this->get("/tm/admin/shop/{$shop->id}/edit")->assertOk()
            ->assertSee('name="delivery_fee"', false)
            ->assertSee('name="pickup_available"', false)
            ->assertSee('name="status"', false);
        $this->get('/tm/admin/shop/create')->assertOk();
    }
}
