<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\UserNotification;
use App\Services\Fcm;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Pushes one in-app notification to every device of its user.
 * Dispatched by UserNotificationObserver when FCM is configured.
 */
class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public UserNotification $notification)
    {
    }

    public function handle(Fcm $fcm): void
    {
        if (!$fcm->configured()) {
            return;
        }

        $devices = Device::where('user_id', $this->notification->user_id)->get();
        if ($devices->isEmpty()) {
            return;
        }

        $locale = config('app.locale', 'tm');
        $data = ['notification_id' => $this->notification->id, 'type' => $this->notification->type]
            + (array) ($this->notification->data ?? []);

        foreach ($devices as $device) {
            $result = $fcm->send(
                $device->device_token,
                $this->notification->title($locale),
                $this->notification->body($locale),
                $data
            );

            // Stale or never-valid tokens (e.g. the random token created at
            // registration) are dropped so we stop retrying them.
            if ($result === 'gone') {
                $device->delete();
            }
        }
    }
}
