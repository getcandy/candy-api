<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBackorderFieldOnVariants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('backorder');
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->enum('backorder', ['in-stock', 'expected', 'always'])->default('always')->after('stock')->index();
        });
    }

    public function down()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('backorder');
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('backorder')->after('stock');
        });
    }
}
