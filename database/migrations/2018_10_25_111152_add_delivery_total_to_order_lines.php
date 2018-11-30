<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryTotalToOrderLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->integer('delivery_total')->after('discount_total')->default(0);
        });
    }

    public function down()
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropColumn('delivery_total');
        });
    }
}
