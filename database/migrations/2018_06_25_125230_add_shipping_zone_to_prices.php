<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingZoneToPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->integer('shipping_zone_id')->unsigned()->nullable()->after('shipping_method_id');
            $table->foreign('shipping_zone_id')->references('id')->on('shipping_zones')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->dropForeign(['shipping_zone_id']);
            $table->dropColumn('shipping_zone_id');
        });
    }
}
