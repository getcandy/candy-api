<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecycleBinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recycle_bin', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('recyclable');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recycle_bin');
    }
}
