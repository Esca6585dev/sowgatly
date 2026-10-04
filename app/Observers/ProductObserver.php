<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\UserNotification;
use App\Models\WaitlistItem;

/**
 * Tells waiting customers when a product becomes available again:
 * stock goes from empty to positive, or the product is switched back on.
 */
class ProductObserver
{
    public function updated(Product $product): void
    {
        $backInStock = $product->wasChanged('stock')
            && (int) $product->getOriginal('stock') <= 0
            && (int) $product->stock > 0;

        $reactivated = $product->wasChanged('status')
            && !$product->getOriginal('status')
            && (bool) $product->status;

        if (!$backInStock && !$reactivated) {
            return;
        }

        // Still unavailable on the other axis: wait for that to flip too.
        if (!$product->status || ($product->stock !== null && (int) $product->stock <= 0)) {
            return;
        }

        WaitlistItem::where('product_id', $product->id)
            ->whereNull('notified_at')
            ->get()
            ->each(function (WaitlistItem $item) use ($product) {
                UserNotification::create([
                    'user_id' => $item->user_id,
                    'type' => 'product_available',
                    'data' => ['product_id' => $product->id],
                ]);
                $item->forceFill(['notified_at' => now()])->save();
            });
    }
}
