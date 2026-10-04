<?php

namespace App\Observers;

use App\Jobs\SendPushNotification;
use App\Models\UserNotification;
use App\Services\Fcm;

class UserNotificationObserver
{
    public function created(UserNotification $notification): void
    {
        if (app(Fcm::class)->configured()) {
            SendPushNotification::dispatch($notification);
        }
    }
}
