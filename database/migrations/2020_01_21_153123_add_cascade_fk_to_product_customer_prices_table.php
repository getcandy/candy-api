<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeFkToProductCustomerPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_customer_prices', function (Blueprint $table) {
            $table->dropForeign('product_customer_prices_product_variant_id_foreign');
            $table->foreign('product_variant_id')
            ->references('id')->on('product_variants')
            ->onDelete('cascade');

            $table->dropForeign('product_customer_prices_customer_group_id_foreign');
            $table->foreign('customer_group_id')
            ->references('id')->on('customer_groups')
            ->onDelete('cascade');
        });
    }

    public function down()
    {
    }
}
