<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSetNullOnDeleteOnOrderLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('order_lines', function (Blueprint $table) {
                $table->dropForeign('order_lines_product_variant_id_foreign');
            });
        } catch (QueryException $e) {
            //
        }
        Schema::table('order_lines', function (Blueprint $table) {
            $table->foreign('product_variant_id')
                ->references('id')->on('product_variants')
                ->onDelete('SET NULL');
        });
    }

    public function down()
    {
        // Schema::dropIfExists('recycle_bin');
    }
}
