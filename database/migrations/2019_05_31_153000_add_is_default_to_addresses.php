<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsDefaultToAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('billing');
        });
    }

    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
}
