<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetTransformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_transforms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transform_id')->unsigned()->index();
            $table->foreign('transform_id')->references('id')->on('transforms');
            $table->integer('asset_id')->unsigned()->index();
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->boolean('file_exists')->default(false);
            $table->string('location');
            $table->string('filename')->unique();
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
        Schema::dropIfExists('asset_transforms');
    }
}
