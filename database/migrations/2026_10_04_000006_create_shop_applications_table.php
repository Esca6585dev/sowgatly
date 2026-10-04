<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Разместить свой магазин": a request from a (possibly anonymous) person to
 * open a shop. Admins contact them and approve or reject from the panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->enum('status', ['new', 'contacted', 'approved', 'rejected'])->default('new');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_applications');
    }
};
