<?php

namespace Tests\Feature\Admin;

use App\Models\ChatThread;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;

class ChatAdminTest extends AdminTestCase
{
    private function thread(string $customer, string $shop, ?int $orderId = null): ChatThread
    {
        return ChatThread::create([
            'user_id' => User::factory()->create(['name' => $customer])->id,
            'shop_id' => Shop::factory()->create(['name' => $shop])->id,
            'order_id' => $orderId,
        ]);
    }

    /** @test */
    public function list_search_unread_filter_and_ajax()
    {
        $a = $this->thread('Aýna Orazowa', 'Gül öýi');
        $a->post('user', $a->user_id, 'Haçan getirersiňiz?');
        $b = $this->thread('Merdan Berdiýew', 'Tort dünýäsi');
        $b->post('user', $b->user_id, 'Salam');
        $b->markReadBy('shop');

        $this->get($this->adminUrl('chat'))->assertOk()
            ->assertSee('Aýna Orazowa')->assertSee('Merdan Berdiýew')->assertSee('Haçan getirersiňiz?');

        $this->get($this->adminUrl('chat?search=Tort'))->assertOk()
            ->assertSee('Merdan Berdiýew')->assertDontSee('Aýna Orazowa');

        // Threads still waiting for the shop's reply.
        $this->get($this->adminUrl('chat?unread=1'))->assertOk()
            ->assertSee('Aýna Orazowa')->assertDontSee('Merdan Berdiýew');

        $this->get($this->adminUrl('chat?search=Aýna'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Aýna Orazowa')->assertDontSee('<html', false);
    }

    /** @test */
    public function thread_shows_bubbles_and_order_link()
    {
        $order = Order::factory()->create();
        $thread = $this->thread('Aýna Orazowa', 'Gül öýi', $order->id);
        $thread->post('user', $thread->user_id, 'Gül näçe?');
        $thread->post('shop', $thread->shop_id, '150 manat');

        $this->get($this->adminUrl("chat/{$thread->id}"))->assertOk()
            ->assertSee('class="bubbles"', false)
            ->assertSee('class="bubble mine"', false)
            ->assertSee('Gül näçe?')->assertSee('150 manat')
            ->assertSee(route('order.show', ['tm', $order->id]), false);
    }

    /** @test */
    public function chats_are_read_only()
    {
        $thread = $this->thread('Aýna', 'Gül');

        $this->get($this->adminUrl('chat/create'))->assertNotFound();
        $this->delete($this->adminUrl("chat/{$thread->id}"))->assertStatus(405);
    }
}
