<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShippingRegions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->boolean('regional')->default(false);
        });

        Schema::create('shipping_regions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->unsigned();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->integer('shipping_zone_id')->unsigned();
            $table->foreign('shipping_zone_id')->references('id')->on('shipping_zones')->onDelete('cascade');
            $table->string('region');
            $table->string('address_field');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->dropColumn('regional');
        });
        Schema::dropIfExists('shipping_regions');
    }
}
