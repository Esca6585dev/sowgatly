<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            // Rating summaries (avg / count per product) read only these two columns.
            $table->index(['product_id', 'rating'], 'product_reviews_product_rating_index');
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex('product_reviews_product_rating_index');
        });
    }
};
