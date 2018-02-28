<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shipping_method_id')->unsigned();
            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods');
            $table->decimal('rate', 10, 2);
            $table->integer('currency_id')->unsigned();
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->boolean('fixed')->default(true);
            $table->decimal('min_weight', 10, 5)->default(0.00)->unsigned();
            $table->string('weight_unit')->default('kg');
            $table->decimal('min_height', 10, 5)->default(0.00)->unsigned();
            $table->string('height_unit')->default('cm');
            $table->decimal('min_width', 10, 5)->default(0.00)->unsigned();
            $table->string('width_unit')->default('cm');
            $table->decimal('min_depth', 10, 5)->default(0.00)->unsigned();
            $table->string('depth_unit')->default('cm');
            $table->decimal('min_volume', 10, 5)->default(0.00)->unsigned();
            $table->string('volume_unit')->default('l');
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
        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->dropForeign(['shipping_method_id']);
        });
        Schema::dropIfExists('shipping_prices');
    }
}
