<?php

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
