<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingIndexesToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channel_product', function (Blueprint $table) {
            $table->index('published_at');
        });
        Schema::table('category_channel', function (Blueprint $table) {
            $table->index('published_at');
        });
        Schema::table('channel_collection', function (Blueprint $table) {
            $table->index('published_at');
        });
        Schema::table('channel_discount', function (Blueprint $table) {
            $table->index('published_at');
        });
        Schema::table('customer_group_product', function (Blueprint $table) {
            $table->index('visible');
            $table->index('purchasable');
        });
    }

    public function down()
    {
        //
    }
}
