<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFraudFieldsToTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('address_matched')->default(false);
            $table->boolean('postcode_matched')->default(false);
            $table->boolean('cvc_matched')->default(false);
            $table->boolean('threed_secure')->default(false);
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('address_matched');
            $table->dropColumn('postcode_matched');
            $table->dropColumn('cvc_matched');
            $table->dropColumn('threed_secure');
        });
    }
}
