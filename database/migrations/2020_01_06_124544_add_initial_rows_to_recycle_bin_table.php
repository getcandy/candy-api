<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddInitialRowsToRecycleBinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get all deleted products.
        $products = DB::table('products')->select('id')->whereNotNull('deleted_at')->get()->map(function ($product) {
            return [
                'recyclable_type' => GetCandy\Api\Core\Products\Models\Product::class,
                'recyclable_id' => $product->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        DB::table('recycle_bin')->insert($products->toArray());
    }

    public function down()
    {
        // Schema::dropIfExists('recycle_bin');
    }
}
