<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManualFlagToOrderLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->integer('is_manual')->default(false)->after('is_shipping');
        });
    }

    public function down()
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropColumn('is_manual');
        });
    }
}
