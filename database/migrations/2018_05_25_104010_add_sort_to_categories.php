<?php

use Illuminate\Support\Facades\Schema;
use GetCandy\Api\Core\Taxes\Models\Tax;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class AddSortToCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('sort')->default('min_price:asc')->index();
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('sort');
        });
    }
}
