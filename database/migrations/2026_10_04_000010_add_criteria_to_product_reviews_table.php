<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            // The three criteria from the design: Соответствие / Цена-качество / Сервис магазина.
            $table->unsignedTinyInteger('rating_match')->nullable()->after('rating');
            $table->unsignedTinyInteger('rating_value')->nullable()->after('rating_match');
            $table->unsignedTinyInteger('rating_service')->nullable()->after('rating_value');
            $table->unsignedBigInteger('order_id')->nullable()->after('product_id');

            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn(['rating_match', 'rating_value', 'rating_service', 'order_id']);
        });
    }
};
