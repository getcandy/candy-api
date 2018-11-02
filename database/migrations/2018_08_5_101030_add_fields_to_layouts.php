<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToLayouts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('layouts', function (Blueprint $table) {
            $table->string('type')->after('handle')->index();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->integer('layout_id')->unsigned()->nullable();
            $table->foreign('layout_id')->references('id')->on('layouts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['layout_id']);
            $table->dropColumn('layout_id');
        });

        Schema::table('layouts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
