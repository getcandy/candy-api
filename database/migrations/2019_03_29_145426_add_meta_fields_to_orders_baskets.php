<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaFieldsToOrdersBaskets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('meta')->nullable();
        });
        Schema::table('order_lines', function (Blueprint $table) {
            $table->json('meta')->nullable();
        });
        Schema::table('baskets', function (Blueprint $table) {
            $table->json('meta')->nullable();
        });
        Schema::table('basket_lines', function (Blueprint $table) {
            $table->json('meta')->nullable();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
        Schema::table('baskets', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
        Schema::table('basket_lines', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
}
