<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

class UserNotification extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'type', 'data', 'read_at'];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /** Every type the API emits, with the keys found in `data`. See docs/notifications.md. */
    public const TYPES = [
        'order_created' => ['order_id', 'status'],
        'order_status' => ['order_id', 'status'],
        'product_available' => ['product_id'],
        'chat_message' => ['thread_id', 'shop_id', 'message_id'],
        'shop_application' => ['application_id', 'status'],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Localized title for the apps and for push. Unknown types fall back to the type name. */
    public function title(?string $locale = null): string
    {
        return $this->text('title', $locale);
    }

    public function body(?string $locale = null): string
    {
        return $this->text('body', $locale);
    }

    private function text(string $part, ?string $locale): string
    {
        $locale = $locale ?: app()->getLocale();
        $data = (array) ($this->data ?? []);
        $params = [
            'number' => str_pad((string) ($data['order_id'] ?? ''), 7, '0', STR_PAD_LEFT),
            'status' => isset($data['status'])
                ? Lang::get('api.order_status.' . $data['status'], [], $locale)
                : '',
        ];

        // order_status texts differ per status when a specific key exists.
        $keys = [];
        if ($this->type === 'order_status' && isset($data['status'])) {
            $keys[] = "api.notifications.order_status_{$data['status']}.{$part}";
        }
        $keys[] = "api.notifications.{$this->type}.{$part}";

        foreach ($keys as $key) {
            if (Lang::has($key, $locale)) {
                return Lang::get($key, $params, $locale);
            }
        }

        return $part === 'title' ? $this->type : '';
    }
}
