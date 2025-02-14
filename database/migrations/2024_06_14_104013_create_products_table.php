<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            $table->string('name_tm');
            $table->string('name_en');
            $table->string('name_ru');

            $table->decimal('price', 10, 2);
            $table->integer('discount')->nullable();
            
            $table->text('description_tm');
            $table->text('description_en');
            $table->text('description_ru');

            $table->integer('production_time')->nullable()->comment('Hemme product time minutda gorkeziler');

            $table->integer('min_order')->nullable();

            $table->integer('stock')->nullable();

            $table->boolean('seller_status')->comment('Bu dukancy tarapyndan berilmeli status');
            $table->boolean('status')->comment('Bu administrator tarapyndan berilmeli status');
            
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            
            $table->foreignId('category_id')->constrained();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};