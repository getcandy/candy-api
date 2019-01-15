<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameVariantOnOrderLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->renameColumn('variant', 'option');
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->integer('product_variant_id')->after('order_id')->unsigned()->nullable();
            $table->foreign('product_variant_id')->references('id')->on('product_variants');
        });
    }

    public function down()
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->renameColumn('option', 'variant');
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
        });
    }
}
