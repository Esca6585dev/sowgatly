<?php

namespace Tests\Feature\Admin;

use App\Models\ChatThread;
use App\Models\Image;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;

class OrderAdminTest extends AdminTestCase
{
    private function order(array $attributes = [], int $quantity = 2): Order
    {
        $shop = Shop::factory()->create(['name' => 'Gül öýi']);
        $user = User::factory()->create(['name' => 'Aýna Orazowa']);
        $product = Product::factory()->create(['shop_id' => $shop->id, 'name_tm' => 'Gyzyl gül', 'stock' => 5]);
        $product->images()->delete();
        Image::create(['product_id' => $product->id, 'url' => 'product/test/rose.jpg']);

        $order = Order::create(array_merge([
            'user_id' => $user->id, 'shop_id' => $shop->id, 'total_amount' => 220, 'items_total' => 200,
            'delivery_fee' => 20, 'status' => 'pending', 'delivery_type' => 'scheduled', 'scheduled_at' => now()->addDay(),
            'recipient_name' => 'Maýsa', 'recipient_phone' => '65656585', 'delivery_address' => 'Aşgabat, Atatürk köç. 5',
            'note' => 'Gutlag kartyny goşuň', 'fulfillment' => 'delivery', 'payment_method' => 'cash', 'payment_status' => 'unpaid',
        ], $attributes));
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => $quantity, 'price' => 100]);

        return $order;
    }

    /** @test */
    public function list_has_status_chips_and_filters()
    {
        $pending = $this->order();
        $done = $this->order(['status' => 'completed', 'payment_status' => 'paid']);

        $this->get($this->adminUrl('order'))->assertOk()
            ->assertSee($pending->number)->assertSee($done->number)
            ->assertSee('class="chips"', false)
            ->assertSee('name="payment_status"', false)
            ->assertSee('name="fulfillment"', false);

        // Status chip keeps the other query parameters.
        $this->get($this->adminUrl('order?payment_status=paid'))->assertOk()
            ->assertSee('status=completed', false)
            ->assertSee('payment_status=paid', false)
            ->assertSee($done->number)->assertDontSee($pending->number);

        $this->get($this->adminUrl('order?status=pending'))->assertOk()
            ->assertSee($pending->number)->assertDontSee($done->number)
            ->assertSee('<input type="hidden" name="status" value="pending">', false);

        // An unknown status is ignored instead of hiding every order.
        $this->get($this->adminUrl('order?status=bogus'))->assertOk()->assertSee($pending->number)->assertSee($done->number);

        // AJAX returns only the table partial.
        $this->get($this->adminUrl('order?search=' . $pending->id), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee($pending->number)->assertDontSee('<html', false);
        $this->get($this->adminUrl('order?search=Gyzyl'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee($pending->number);
    }

    /** @test */
    public function shop_filter_from_the_shop_page_is_kept()
    {
        $mine = $this->order();
        $other = $this->order();

        $this->get($this->adminUrl('order?shop_id=' . $mine->shop_id))->assertOk()
            ->assertSee($mine->number)->assertDontSee($other->number)
            ->assertSee('<input type="hidden" name="shop_id" value="' . $mine->shop_id . '">', false)
            // Status chips keep the shop filter too.
            ->assertSee('shop_id=' . $mine->shop_id . '&amp;status=pending', false);

        $this->get($this->adminUrl('order?shop_id=' . $mine->shop_id . '&search=Gyzyl'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee($mine->number)->assertDontSee($other->number);
    }

    /** @test */
    public function detail_shows_items_totals_delivery_and_only_allowed_transitions()
    {
        $order = $this->order();
        $thread = ChatThread::create(['user_id' => $order->user_id, 'shop_id' => $order->shop_id, 'order_id' => $order->id]);

        $this->get($this->adminUrl("order/{$order->id}"))->assertOk()
            ->assertSee('Gyzyl gül')
            ->assertSee(asset('product/test/rose.jpg'), false)
            ->assertSee('200.00 TMT')->assertSee('20.00 TMT')->assertSee('220.00 TMT')
            ->assertSee('Maýsa')->assertSee('Aşgabat, Atatürk köç. 5')->assertSee('Gutlag kartyny goşuň')
            ->assertSee('Aýna Orazowa')->assertSee('Gül öýi')
            ->assertSee(route('chat.show', ['tm', $thread->id]), false)
            ->assertSee('<option value="processing"', false)
            ->assertSee('<option value="cancelled"', false)
            ->assertDontSee('<option value="completed"', false)
            ->assertDontSee('<option value="delivering"', false);

        // A closed order offers no transitions.
        $closed = $this->order(['status' => 'completed']);
        $this->get($this->adminUrl("order/{$closed->id}"))->assertOk()
            ->assertDontSee('<option value="cancelled"', false)
            ->assertDontSee('<option value="processing"', false);
    }

    /** @test */
    public function cancelling_restocks_and_impossible_transitions_are_refused()
    {
        $order = $this->order([], 2);
        $product = $order->items()->first()->product;

        $this->put($this->adminUrl("order/{$order->id}"), ['status' => 'cancelled'])
            ->assertRedirect($this->adminUrl("order/{$order->id}"));
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(7, $product->fresh()->stock);

        $this->put($this->adminUrl("order/{$order->id}"), ['status' => 'processing'])->assertSessionHas('error');
        $this->assertEquals('cancelled', $order->fresh()->status);

        $this->put($this->adminUrl("order/{$order->id}"), ['status' => 'lost'])->assertSessionHasErrors('status');
        $this->put($this->adminUrl("order/{$order->id}"), ['payment_status' => 'stolen'])->assertSessionHasErrors('payment_status');
    }
}
