<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraEmailFieldsToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('billing_email')->after('billing_phone')->nullable()->index();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_email')->after('shipping_phone')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_email');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('billing_email');
        });
    }
}
