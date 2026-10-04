<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'total_amount',
        'status',
        'delivery_type',
        'scheduled_at',
        'recipient_phone',
        'recipient_name',
        'delivery_address',
        'note',
        'fulfillment',
        'items_total',
        'delivery_fee',
        'payment_method',
        'payment_bank',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'paid_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'items_total' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
    ];

    /**
     * "number" is the zero-padded id shown to customers ("Заказ № 0000001").
     * Appended so every existing order payload gains it without shape changes.
     */
    protected $appends = ['number'];

    public const STATUSES = ['pending', 'processing', 'delivering', 'completed', 'cancelled'];

    /**
     * Allowed status changes. Customers may only cancel a pending order;
     * the shop moves it forward or cancels it.
     */
    public const TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['delivering', 'completed', 'cancelled'],
        'delivering' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function getNumberAttribute(): string
    {
        return str_pad((string) $this->id, 7, '0', STR_PAD_LEFT);
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            if (ctype_digit($term)) {
                $q->orWhere('id', (int) $term);
            }
            $like = '%' . $term . '%';
            $q->orWhereHas('items.product', function ($p) use ($like) {
                $p->where('name_tm', 'like', $like)
                  ->orWhere('name_ru', 'like', $like)
                  ->orWhere('name_en', 'like', $like);
            });
        });
    }

    protected static function booted()
    {
        // Every order event the customer cares about becomes an in-app
        // notification.
        static::created(function (Order $order) {
            $order->notifyCustomer('order_created');
        });

        static::updated(function (Order $order) {
            if ($order->wasChanged('status')) {
                $order->notifyCustomer('order_status');
            }
        });
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /**
     * Put the ordered quantities back into stock (used when cancelling).
     */
    public function restock(): void
    {
        foreach ($this->items()->with('product')->get() as $item) {
            if ($item->product && $item->product->stock !== null) {
                $item->product->increment('stock', $item->quantity);
            }
        }
    }

    private function notifyCustomer(string $type): void
    {
        UserNotification::create([
            'user_id' => $this->user_id,
            'type' => $type,
            'data' => ['order_id' => $this->id, 'status' => $this->status],
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function chatThread()
    {
        return $this->hasOne(ChatThread::class);
    }
}