<?php

namespace Tests\Feature\Admin;

use App\Models\Message;
use App\Models\User;

class MessageAdminTest extends AdminTestCase
{
    private function message(array $attributes = []): Message
    {
        return Message::create(array_merge([
            'username' => 'Maýsa Orazowa', 'email' => 'maysa@example.com',
            'phone_number' => '+99365112233', 'message' => 'Dükany nädip açyp bolar?',
        ], $attributes));
    }

    /** @test */
    public function list_and_search()
    {
        $this->message();
        $this->message(['username' => 'Dmitry Petrov', 'email' => 'dmitry@example.ru', 'message' => 'Заказ опоздал']);

        $this->get($this->adminUrl('message'))->assertOk()
            ->assertSee('Maýsa Orazowa')->assertSee('Dmitry Petrov')->assertSee('maysa@example.com');

        $this->get($this->adminUrl('message?search=опоздал'))->assertOk()
            ->assertSee('Dmitry Petrov')->assertDontSee('Maýsa Orazowa');

        // AJAX search (used to crash on a protected static call) returns only the partial.
        $this->get($this->adminUrl('message?search=maysa@'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Maýsa Orazowa')->assertDontSee('Dmitry Petrov')->assertDontSee('<html', false);
    }

    /** @test */
    public function show_and_delete()
    {
        $user = User::factory()->create(['name' => 'Hasaply ulanyjy']);
        $message = $this->message(['user_id' => $user->id]);

        $this->get($this->adminUrl("message/{$message->id}"))->assertOk()
            ->assertSee('Dükany nädip açyp bolar?')->assertSee('+99365112233')
            ->assertSee('Hasaply ulanyjy')->assertSee('mailto:maysa@example.com', false);

        $this->delete($this->adminUrl("message/{$message->id}"))
            ->assertRedirect($this->adminUrl('message'))->assertSessionHas('success-delete');
        $this->assertNull($message->fresh());
    }

    /** @test */
    public function create_and_edit_redirect_to_the_list()
    {
        $message = $this->message();

        $this->get($this->adminUrl('message/create'))->assertRedirect($this->adminUrl('message'));
        $this->post($this->adminUrl('message'), ['username' => 'X', 'message' => 'Y'])->assertRedirect($this->adminUrl('message'));
        $this->get($this->adminUrl("message/{$message->id}/edit"))->assertRedirect($this->adminUrl('message'));
        $this->put($this->adminUrl("message/{$message->id}"), ['message' => 'changed'])->assertRedirect($this->adminUrl('message'));

        $this->assertEquals(1, Message::count());
        $this->assertEquals('Dükany nädip açyp bolar?', $message->fresh()->message);
    }
}
