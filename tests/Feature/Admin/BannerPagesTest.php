<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = Admin::create([
            'first_name' => 'Admin', 'last_name' => 'Test', 'username' => 'admin-banner',
            'email' => 'admin-banner@example.com', 'password' => bcrypt('secret'),
        ]);
        $this->actingAs($admin, 'admin');
    }

    /** @test */
    public function banners_can_be_created_edited_and_deleted(): void
    {
        Storage::fake('public');

        $this->get('/tm/admin/banner')->assertOk()->assertSee('Häzirlikçe banner ýok');
        $this->get('/tm/admin/banner/create')->assertOk()->assertSee('name="title_tm"', false);

        $this->post('/tm/admin/banner', [
            'title_tm' => 'Täze ýyl',
            'title_ru' => 'С Новым годом',
            'link_type' => 'category',
            'link_value' => '3',
            'position' => 1,
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('banner.jpg', 1125, 441),
        ])->assertRedirect('/tm/admin/banner');

        $banner = Banner::firstOrFail();
        $this->assertSame('С Новым годом', $banner->title_ru);
        $this->assertSame('category', $banner->link_type);
        $this->assertTrue($banner->is_active);
        $this->assertStringStartsWith('storage/banners/', $banner->image);
        Storage::disk('public')->assertExists(substr($banner->image, strlen('storage/')));

        $this->get('/tm/admin/banner')->assertOk()->assertSee('Täze ýyl');
        $this->get("/tm/admin/banner/{$banner->id}/edit")->assertOk()->assertSee('С Новым годом');

        $this->put("/tm/admin/banner/{$banner->id}", [
            'title_tm' => 'Täze ýyl 2027',
            'link_type' => 'none',
            'is_active' => '0',
        ])->assertRedirect('/tm/admin/banner');
        $banner->refresh();
        $this->assertSame('Täze ýyl 2027', $banner->title_tm);
        $this->assertFalse($banner->is_active);
        $this->assertNotNull($banner->image); // untouched when no new file is sent

        $this->delete("/tm/admin/banner/{$banner->id}")->assertRedirect('/tm/admin/banner');
        $this->assertDatabaseCount('banners', 0);
    }

    /** @test */
    public function a_link_needs_a_value_and_the_title_is_required(): void
    {
        $this->from('/tm/admin/banner/create')
            ->post('/tm/admin/banner', ['title_tm' => '', 'link_type' => 'product', 'link_value' => ''])
            ->assertRedirect('/tm/admin/banner/create')
            ->assertSessionHasErrors(['title_tm', 'link_value']);
    }
}
