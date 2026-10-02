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
        'delivery_address',
        'note',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    /**
     * Allowed status changes. Customers may only cancel a pending order;
     * the shop moves it forward or cancels it.
     */
    public const TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

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
}