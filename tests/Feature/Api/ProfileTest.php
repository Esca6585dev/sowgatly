<?php

namespace Tests\Feature\Api;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileTest extends ApiTestCase
{
    /** @test */
    public function user_can_set_birth_date_and_upload_an_avatar_file()
    {
        Storage::fake('public');

        $response = $this->post('/api/users/me', [
            '_method' => 'PUT',
            'name' => 'Aýna',
            'birth_date' => '1995-04-21',
            'image' => UploadedFile::fake()->image('me.jpg'),
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('user.name', 'Aýna')
            ->assertJsonPath('user.birth_date', '1995-04-21');

        $path = $this->user->fresh()->image;
        $this->assertStringStartsWith('storage/users/' . $this->user->id . '/', $path);
        Storage::disk('public')->assertExists(substr($path, strlen('storage/')));

        // Removing the avatar deletes the file.
        $this->deleteJson('/api/users/me/image')->assertOk()->assertJsonPath('user.image', null);
        Storage::disk('public')->assertMissing(substr($path, strlen('storage/')));
    }

    /** @test */
    public function user_can_upload_a_base64_avatar()
    {
        Storage::fake('public');
        // 1x1 transparent PNG.
        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

        $this->putJson('/api/users/me', ['name' => 'Aýna', 'image' => $png])->assertOk();
        $this->assertStringStartsWith('storage/users/', $this->user->fresh()->image);
    }

    /** @test */
    public function birth_date_must_be_in_the_past()
    {
        $this->putJson('/api/users/me', ['name' => 'Aýna', 'birth_date' => now()->addDay()->toDateString()])->assertStatus(422);
    }

    /** @test */
    public function notifications_unread_count_endpoint_works()
    {
        $this->getJson('/api/me/notifications/unread-count')->assertOk()->assertJsonPath('unread', 0);
    }
}
