<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Minimal Firebase Cloud Messaging HTTP v1 client (service-account auth).
 *
 * Configure FCM_SERVICE_ACCOUNT_FILE; without it `configured()` is false and
 * nothing is sent, so the API works in every environment.
 */
class Fcm
{
    public const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    /** Device tokens FCM reports as gone; callers should delete them. */
    public const GONE = ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'];

    private ?array $account = null;

    public function configured(): bool
    {
        $file = config('services.fcm.service_account_file');

        return is_string($file) && $file !== '' && is_readable($file);
    }

    /**
     * Send one notification to one device.
     *
     * @param  array<string, scalar|null>  $data  Extra key/values (sent as strings).
     * @return string 'sent', 'gone' (token should be deleted) or 'failed'
     */
    public function send(string $deviceToken, string $title, string $body, array $data = []): string
    {
        $message = [
            'token' => $deviceToken,
            'notification' => ['title' => $title, 'body' => $body],
            'data' => collect($data)->map(fn ($v) => (string) $v)->all(),
            'android' => ['priority' => 'high'],
        ];

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post(sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $this->projectId()), [
                'message' => $message,
            ]);

        if ($response->successful()) {
            return 'sent';
        }

        $status = (string) $response->json('error.status', '');
        $code = (string) data_get($response->json('error.details', []), '0.errorCode', '');

        if ($response->status() === 404 || in_array($status, self::GONE, true) || $code === 'UNREGISTERED') {
            return 'gone';
        }

        Log::warning('FCM send failed', ['status' => $response->status(), 'body' => $response->body()]);

        return 'failed';
    }

    public function projectId(): string
    {
        return (string) (config('services.fcm.project_id') ?: $this->account()['project_id'] ?? '');
    }

    /** OAuth2 access token from a signed JWT, cached a little short of its hour. */
    public function accessToken(): string
    {
        return Cache::remember('fcm:access_token', 3300, function () {
            $account = $this->account();
            $now = time();

            $jwt = $this->encode(['alg' => 'RS256', 'typ' => 'JWT'])
                . '.' . $this->encode([
                    'iss' => $account['client_email'],
                    'scope' => self::SCOPE,
                    'aud' => $account['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                    'iat' => $now,
                    'exp' => $now + 3600,
                ]);

            $signature = '';
            if (!openssl_sign($jwt, $signature, $account['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Could not sign the FCM JWT with the service-account key');
            }
            $jwt .= '.' . $this->base64Url($signature);

            $response = Http::asForm()->post($account['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (!$response->successful() || !$response->json('access_token')) {
                throw new RuntimeException('FCM token request failed: ' . $response->body());
            }

            return (string) $response->json('access_token');
        });
    }

    private function account(): array
    {
        if ($this->account === null) {
            $json = json_decode((string) file_get_contents(config('services.fcm.service_account_file')), true);
            if (!is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
                throw new RuntimeException('FCM service-account file is not a valid Google service account');
            }
            $this->account = $json;
        }

        return $this->account;
    }

    private function encode(array $data): string
    {
        return $this->base64Url(json_encode($data, JSON_UNESCAPED_SLASHES));
    }

    private function base64Url(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
}
