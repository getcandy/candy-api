<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingZoneExclusionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_exclusions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shipping_zone_id')->unsigned();
            $table->foreign('shipping_zone_id')->references('id')->on('shipping_zones');
            $table->morphs('excludable');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_exclusions');
    }
}
