<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeShippingPriceToInt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->integer('min_basket_new')->unsigned()->default(0)->after('rate');
        });

        DB::table('shipping_prices')->update(['min_basket_new' => DB::raw('100 * min_basket')]);

        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->dropColumn('min_basket');
        });

        Schema::table('shipping_prices', function (Blueprint $table) {
            $table->renameColumn('min_basket_new', 'min_basket');
        });
    }
}
