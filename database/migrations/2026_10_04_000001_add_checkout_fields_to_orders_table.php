<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Checkout options from the Figma design: delivery or pickup, a delivery fee,
 * a payment method with an optional bank, and the "delivering" order status.
 * Everything has a default so existing rows stay valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('fulfillment', ['delivery', 'pickup'])->default('delivery')->after('status');
            $table->decimal('items_total', 10, 2)->nullable()->after('total_amount');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('items_total');
            $table->enum('payment_method', ['cash', 'online'])->default('cash')->after('delivery_fee');
            $table->string('payment_bank', 50)->nullable()->after('payment_method');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid')->after('payment_bank');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->string('recipient_name')->nullable()->after('recipient_phone');
        });

        // The original create migration already lists "delivering" for fresh
        // installs (SQLite cannot alter a CHECK constraint); existing MySQL
        // databases need the enum widened in place.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'processing', 'delivering', 'completed', 'cancelled') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('orders')->where('status', 'delivering')->update(['status' => 'processing']);
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL");
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'fulfillment', 'items_total', 'delivery_fee', 'payment_method',
                'payment_bank', 'payment_status', 'paid_at', 'recipient_name',
            ]);
        });
    }
};
