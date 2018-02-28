<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountCriteriaItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_criteria_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('discount_criteria_set_id')->unsigned();
            $table->foreign('discount_criteria_set_id')->references('id')->on('discount_criteria_sets')->onDelete('cascade');
            $table->string('type')->index();
            $table->string('value')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('discount_criteria_items', function (Blueprint $table) {
            $table->dropForeign(['discount_criteria_set_id']);
        });
        Schema::dropIfExists('discount_criteria_items');
    }
}
