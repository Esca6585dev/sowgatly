<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('brands_products', function (Blueprint $table) {
            $table->id();

            $table->unsignedBiginteger('brands_id')->unsigned();
            $table->unsignedBiginteger('products_id')->unsigned();

            $table->foreign('brands_id')->references('id')
                 ->on('brands')->onDelete('cascade');
            $table->foreign('products_id')->references('id')
                ->on('products')->onDelete('cascade');

        $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('brands_products');
    }
};