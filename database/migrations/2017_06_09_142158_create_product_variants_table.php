<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->string('sku')->unique();
            $table->json('options')->nullable();
            $table->decimal('price', 10, 2)->unsigned();
            $table->integer('stock')->unsigned();
            $table->boolean('backorder')->default(false);
            $table->boolean('requires_shipping')->default(true);

            // Weights and stuff
            $table->decimal('weight_value', 10, 5)->default(0.00)->unsigned();
            $table->string('weight_unit')->default('kg');
            $table->decimal('height_value', 10, 5)->default(0.00)->unsigned();
            $table->string('height_unit')->default('cm');
            $table->decimal('width_value', 10, 5)->default(0.00)->unsigned();
            $table->string('width_unit')->default('cm');
            $table->decimal('depth_value', 10, 5)->default(0.00)->unsigned();
            $table->string('depth_unit')->default('cm');
            $table->decimal('volume_value', 10, 5)->default(0.00)->unsigned();
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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::dropIfExists('product_variants');
    }
}
