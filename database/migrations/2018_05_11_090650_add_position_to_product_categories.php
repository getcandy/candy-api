<?php

use Illuminate\Support\Facades\Schema;
use GetCandy\Api\Core\Taxes\Models\Tax;
use Illuminate\Database\Schema\Blueprint;
use GetCandy\Api\Core\Orders\Models\Order;
use Illuminate\Database\Migrations\Migration;
use GetCandy\Api\Core\Orders\Models\OrderLine;

class AddPositionToProductCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->integer('position')->default(1)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
}
