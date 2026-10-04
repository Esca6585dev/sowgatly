<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // 20 TMT is the fee shown in the design; each shop can change it.
            $table->decimal('delivery_fee', 10, 2)->default(20)->after('region_id');
            $table->boolean('pickup_available')->default(false)->after('delivery_fee');
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('pickup_available');
            $table->string('phone', 20)->nullable()->after('email');
            $table->text('description_tm')->nullable()->after('min_order_amount');
            $table->text('description_ru')->nullable()->after('description_tm');
            $table->text('description_en')->nullable()->after('description_ru');
            // Existing shops were created by the admins, so they count as approved.
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->after('description_en');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_fee', 'pickup_available', 'min_order_amount', 'phone',
                'description_tm', 'description_ru', 'description_en', 'status',
            ]);
        });
    }
};
