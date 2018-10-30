<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountCriteriaSetModels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_criteria_models', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('discount_criteria_item_id')->unsigned();
            $table->foreign('discount_criteria_item_id')->references('id')->on('discount_criteria_items');
            $table->morphs('eligible');
            $table->unsignedInteger('created_by')->nullable();
            $table->ipAddress('created_ip')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->ipAddress('updated_ip')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->softDeletes();
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
        Schema::table('discount_criteria_models', function (Blueprint $table) {
            $table->dropForeign(['discount_criteria_item_id']);
        });
        Schema::dropIfExists('discount_criteria_models');
    }
}
