<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIndexToLanguagesDefaultColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->index(['default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropIndex(['default']);
        });
    }
}
