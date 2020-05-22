<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSetNullOnDeleteOnBasketLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('basket_lines', function (Blueprint $table) {
            $table->dropForeign('basket_lines_product_variant_id_foreign');
            $table->foreign('product_variant_id')
            ->references('id')->on('product_variants')
            ->onDelete('cascade');
        });
    }

    public function down()
    {
    }
}
