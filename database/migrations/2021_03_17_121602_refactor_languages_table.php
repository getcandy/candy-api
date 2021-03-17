<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('iso');
        });
        Schema::table('languages', function (Blueprint $table) {
            $table->renameColumn('lang', 'code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // One way migration
    }
}
