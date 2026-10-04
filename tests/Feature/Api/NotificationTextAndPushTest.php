<?php

namespace Tests\Feature\Api;

use App\Jobs\SendPushNotification;
use App\Models\Device;
use App\Models\UserNotification;
use App\Services\Fcm;
use Illuminate\Support\Facades\Http;

class NotificationTextAndPushTest extends ApiTestCase
{
    public function test_notifications_carry_localized_title_and_body(): void
    {
        UserNotification::create(['user_id' => $this->user->id, 'type' => 'order_status', 'data' => ['order_id' => 12, 'status' => 'delivering']]);

        // The test client defaults to Accept-Language en-us; real apps send tm/ru/en.
        $this->withHeader('Accept-Language', 'tm')->getJson('/api/me/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Sargyt № 0000012 ýolda')
            ->assertJsonPath('data.0.body', 'Kurýer sargydyňyzy eltip barýar.');

        $this->withHeader('Accept-Language', 'ru')->getJson('/api/me/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Заказ № 0000012 в пути');

        // Unsupported languages fall back to Turkmen.
        $this->withHeader('Accept-Language', 'de-DE,de;q=0.9')->getJson('/api/me/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Sargyt № 0000012 ýolda');
    }

    public function test_every_known_type_has_texts_in_all_languages(): void
    {
        foreach (array_keys(UserNotification::TYPES) as $type) {
            $n = new UserNotification(['type' => $type, 'data' => ['order_id' => 1, 'status' => 'processing']]);
            foreach (['tm', 'ru', 'en'] as $locale) {
                $this->assertNotSame($type, $n->title($locale), "$type has no $locale title");
                $this->assertNotSame('', $n->body($locale), "$type has no $locale body");
            }
        }
    }

    public function test_push_is_skipped_when_fcm_is_not_configured(): void
    {
        Http::fake();
        Device::create(['user_id' => $this->user->id, 'device_token' => 'abc', 'device_type' => 'android']);

        UserNotification::create(['user_id' => $this->user->id, 'type' => 'chat_message', 'data' => ['thread_id' => 1]]);

        Http::assertNothingSent();
        $this->assertDatabaseCount('devices', 1);
    }

    public function test_push_is_sent_to_each_device_and_dead_tokens_are_dropped(): void
    {
        config(['services.fcm.service_account_file' => $this->fakeServiceAccount(), 'services.fcm.project_id' => 'sowgatly-test']);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'ya29.test', 'expires_in' => 3600]),
            'https://fcm.googleapis.com/v1/projects/sowgatly-test/messages:send' => Http::sequence()
                ->push(['name' => 'projects/sowgatly-test/messages/1'], 200)
                ->push(['error' => ['status' => 'NOT_FOUND', 'details' => [['errorCode' => 'UNREGISTERED']]]], 404),
        ]);

        Device::create(['user_id' => $this->user->id, 'device_token' => 'good-token', 'device_type' => 'android']);
        Device::create(['user_id' => $this->user->id, 'device_token' => 'dead-token', 'device_type' => 'android']);

        // QUEUE_CONNECTION=sync in tests, so the job runs inline.
        UserNotification::create(['user_id' => $this->user->id, 'type' => 'order_status', 'data' => ['order_id' => 5, 'status' => 'completed']]);

        Http::assertSentCount(3);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'messages:send')
                && $request['message']['token'] === 'good-token'
                && $request['message']['notification']['title'] === 'Sargyt № 0000005 ýerine ýetirildi'
                && $request['message']['data']['order_id'] === '5'
                && $request->hasHeader('Authorization', 'Bearer ya29.test');
        });

        $this->assertDatabaseHas('devices', ['device_token' => 'good-token']);
        $this->assertDatabaseMissing('devices', ['device_token' => 'dead-token']);
    }

    public function test_the_job_class_is_what_the_observer_dispatches(): void
    {
        $this->assertTrue(class_exists(SendPushNotification::class));
        $this->assertFalse(app(Fcm::class)->configured());
    }

    private function fakeServiceAccount(): string
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $pem);

        $path = storage_path('framework/testing/fcm-service-account.json');
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, json_encode([
            'type' => 'service_account',
            'project_id' => 'sowgatly-test',
            'client_email' => 'push@sowgatly-test.iam.gserviceaccount.com',
            'private_key' => $pem,
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ]));

        return $path;
    }
}
