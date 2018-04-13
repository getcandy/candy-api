<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingMethodZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_method_zones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shipping_method_id')->unsigned();
            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods');
            $table->integer('shipping_zone_id')->unsigned();
            $table->foreign('shipping_zone_id')->references('id')->on('shipping_zones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_method_zones');
    }
}
