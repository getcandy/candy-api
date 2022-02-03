<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthCodeToReusablePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('reusable_payments', function (Blueprint $table) {
            $table->string('auth_code')->after('token')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('reusable_payments', function (Blueprint $table) {
            $table->dropColumn('auth_code');
        });
    }
}
