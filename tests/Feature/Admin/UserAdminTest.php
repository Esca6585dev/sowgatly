<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserAdminTest extends AdminTestCase
{
    /** @test */
    public function list_search_and_filters_render()
    {
        $owner = User::factory()->create(['name' => 'Aýna Owezowa', 'phone_number' => '65111111', 'status' => 1]);
        Shop::factory()->create(['user_id' => $owner->id, 'name' => 'Aýna gülleri']);
        User::factory()->create(['name' => 'Bägül Annaýewa', 'phone_number' => '62222222', 'status' => 0]);

        $this->get($this->adminUrl('user'))->assertOk()->assertSee('Aýna Owezowa')->assertSee('Bägül Annaýewa')->assertSee('Aýna gülleri');
        $this->get($this->adminUrl('user?status=0'))->assertOk()->assertSee('Bägül Annaýewa')->assertDontSee('Aýna Owezowa');
        $this->get($this->adminUrl('user?has_shop=1'))->assertOk()->assertSee('Aýna Owezowa')->assertDontSee('Bägül Annaýewa');
        $this->get($this->adminUrl('user?has_shop=0'))->assertOk()->assertSee('Bägül Annaýewa')->assertDontSee('Aýna Owezowa');

        // AJAX search by phone (with the +993 prefix) returns only the table partial.
        $this->get($this->adminUrl('user?search=' . urlencode('+993 62 22 22 22')), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Bägül Annaýewa')->assertDontSee('Aýna Owezowa')->assertDontSee('<html', false);
    }

    /** @test */
    public function create_store_show_edit_update_and_delete()
    {
        Storage::fake('public');

        $this->get($this->adminUrl('user/create'))->assertOk()->assertSee('+993');

        $this->post($this->adminUrl('user'), [
            'name' => 'Merdan Ataýew', 'phone_number' => '+993 65 12 34 56', 'email' => 'merdan@example.com',
            'birth_date' => '1995-04-12', 'status' => '1', 'image' => UploadedFile::fake()->image('a.jpg'),
        ])->assertRedirect();

        $user = User::where('phone_number', '65123456')->firstOrFail();
        $this->assertSame('Merdan Ataýew', $user->name);
        $this->assertSame('1995-04-12', $user->birth_date->format('Y-m-d'));
        $this->assertTrue((bool) $user->status);
        $this->assertNull($user->password, 'Customers sign in by OTP; the admin does not set a password.');
        $this->assertStringStartsWith('storage/users/', $user->image);
        Storage::disk('public')->assertExists(substr($user->image, strlen('storage/')));

        UserAddress::create(['user_id' => $user->id, 'title' => 'Öý', 'address' => 'Aşgabat, Bitarap Türkmenistan 12', 'is_default' => true]);
        $shop = Shop::factory()->create(['name' => 'Sowgat dükany']);
        Order::create(['user_id' => $user->id, 'shop_id' => $shop->id, 'total_amount' => 150, 'status' => 'completed']);
        Order::create(['user_id' => $user->id, 'shop_id' => $shop->id, 'total_amount' => 99, 'status' => 'cancelled']);

        $this->get($this->adminUrl("user/{$user->id}"))->assertOk()
            ->assertSee('Merdan Ataýew')->assertSee('Bitarap Türkmenistan 12')->assertSee('Sowgat dükany')->assertSee('150 TMT');
        $this->get($this->adminUrl("user/{$user->id}/edit"))->assertOk()->assertSee('merdan@example.com');

        $this->put($this->adminUrl("user/{$user->id}"), [
            'name' => 'Merdan A.', 'phone_number' => '65123456', 'email' => '', 'birth_date' => '', 'status' => '0',
        ])->assertRedirect($this->adminUrl("user/{$user->id}"));
        $user->refresh();
        $this->assertSame('Merdan A.', $user->name);
        $this->assertNull($user->email);
        $this->assertFalse((bool) $user->status);

        $this->delete($this->adminUrl("user/{$user->id}"))->assertRedirect($this->adminUrl('user'));
        $this->assertNull($user->fresh());
    }

    /** @test */
    public function validation_rejects_bad_and_duplicate_phone_numbers()
    {
        User::factory()->create(['phone_number' => '65000000']);

        $this->from($this->adminUrl('user/create'))->post($this->adminUrl('user'), ['name' => '', 'phone_number' => '123', 'email' => 'nope'])
            ->assertRedirect($this->adminUrl('user/create'))
            ->assertSessionHasErrors(['name', 'phone_number', 'email']);

        $this->post($this->adminUrl('user'), ['name' => 'X', 'phone_number' => '65000000'])->assertSessionHasErrors('phone_number');
    }

    /** @test */
    public function a_shop_owner_cannot_be_deleted_before_the_shop()
    {
        $owner = User::factory()->create();
        Shop::factory()->create(['user_id' => $owner->id]);

        $this->delete($this->adminUrl("user/{$owner->id}"))->assertRedirect()->assertSessionHas('warning');
        $this->assertNotNull($owner->fresh());
    }

    /** @test */
    public function users_can_be_exported_as_csv_with_the_list_filters()
    {
        \App\Models\User::factory()->create(['name' => 'Aýna Orazowa', 'status' => 1]);
        \App\Models\User::factory()->create(['name' => 'Merdan Annaýew', 'status' => 0]);

        $response = $this->get($this->adminUrl('export?status=1'));
        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Aýna Orazowa', $csv);
        $this->assertStringNotContainsString('Merdan Annaýew', $csv);
    }
}
