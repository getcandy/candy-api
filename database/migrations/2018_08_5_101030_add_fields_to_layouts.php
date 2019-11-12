<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('type')->nullable()->after('handle')->index();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->integer('layout_id')->unsigned()->nullable();
            $table->foreign('layout_id')->references('id')->on('layouts');
        });
    }
}
