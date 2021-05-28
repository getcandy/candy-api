<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToLanguageCodes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->unique(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropIndex(['code']);
        });
    }
}
