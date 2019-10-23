<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalFieldsToTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('deferred')->default(false)->index();
            $table->timestamp('released_at')->index()->nullable();
            $table->boolean('should_void')->index()->default(false);
            $table->timestamp('voided_at')->index()->nullable();
            $table->string('voided_reason')->nullable();
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('deferred');
            $table->dropColumn('released_at');
            $table->dropColumn('should_void');
            $table->dropColumn('voided_at');
            $table->dropColumn('voided_reason');
        });
    }
}
