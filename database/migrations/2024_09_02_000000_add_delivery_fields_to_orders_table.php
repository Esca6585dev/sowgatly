<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryFieldsToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('delivery_type', ['asap', 'scheduled'])->default('asap')->after('status');
            $table->dateTime('scheduled_at')->nullable()->after('delivery_type');
            $table->string('recipient_phone')->nullable()->after('scheduled_at');
            $table->text('note')->nullable()->after('recipient_phone');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_type', 'scheduled_at', 'recipient_phone', 'note']);
        });
    }
}
