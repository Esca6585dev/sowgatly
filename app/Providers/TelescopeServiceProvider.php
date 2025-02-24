<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if (app()->environment('local')) {  // Allow everything in local
                return true;
            }

            // In production, only allow specific entries (if needed)
            return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });
    }

    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($admin) {
            // Who can view Telescope?

            if (app()->environment('local')) { // Allow access in local
                return true;
            }

            // Example 1: Allow specific emails (for production):
            $allowedEmails = [
                'esca656585@gmail.com',
                // ... other admin emails
            ];
            if (in_array($admin->email, $allowedEmails)) {
                return true;
            }

            return false; // Nobody else can view Telescope
        });
    }
}