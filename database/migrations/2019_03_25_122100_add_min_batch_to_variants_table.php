<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinBatchToVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('min_batch')->unsigned()->default(1)->after('min_qty');
        });
    }

    public function down()
    {
        Schema::table('order_lines', function (Blueprint $table) {
            $table->dropColumn('min_batch');
        });
    }
}
