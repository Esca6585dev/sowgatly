<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title_tm');
            $table->string('title_ru')->nullable();
            $table->string('title_en')->nullable();
            $table->string('subtitle_tm')->nullable();
            $table->string('subtitle_ru')->nullable();
            $table->string('subtitle_en')->nullable();
            $table->string('image')->nullable();
            $table->enum('link_type', ['none', 'category', 'product', 'shop', 'url'])->default('none');
            $table->string('link_value')->nullable();
            // NULL = shown in every city.
            $table->unsignedBigInteger('region_id')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();

            $table->foreign('region_id')->references('id')->on('regions')->nullOnDelete();
            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
