<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // $table->index('placed_at');
            $table->index('sub_total');
            $table->index('shipping_method');
            $table->index('shipping_preference');
            $table->index('currency');
            $table->index('order_total');
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->index('is_shipping');
            $table->index('is_manual');
            $table->index('quantity');
            $table->index('tax_rate');
            $table->index('line_total');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // $table->dropIndex(['placed_at']);
            $table->dropIndex(['sub_total']);
            $table->dropIndex(['shipping_method']);
            $table->dropIndex(['shipping_preference']);
            $table->dropIndex(['currency']);
            $table->dropIndex(['order_total']);
        });

        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropIndex(['is_shipping']);
            $table->dropIndex(['is_manual']);
            $table->dropIndex(['quantity']);
            $table->dropIndex(['tax_rate']);
            $table->dropIndex(['line_total']);
        });
    }
}
