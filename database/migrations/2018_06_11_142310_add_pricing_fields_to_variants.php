<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPricingFieldsToVariants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('unit_qty')->after('price')->unsigned()->default(1);
            $table->integer('min_qty')->after('unit_qty')->unsigned()->default(1);
            $table->integer('max_qty')->after('min_qty')->unsigned()->default(0);
        });
    }

    public function down()
    {
        Schema::drop('saved_baskets');
    }
}
