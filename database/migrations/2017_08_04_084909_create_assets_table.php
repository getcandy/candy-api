<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('asset_source_id')->unsigned()->index()->nullable();
            $table->foreign('asset_source_id')->references('id')->on('asset_sources');
            $table->integer('position')->default(0);
            $table->string('location');
            $table->morphs('assetable');
            $table->boolean('primary')->default(false);
            $table->string('kind')->index();
            $table->string('sub_kind')->nullable()->index();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->string('title')->nullable();
            $table->string('original_filename')->nullable();
            $table->text('caption')->nullable();
            $table->integer('size')->nullable();
            $table->boolean('external')->default(false);
            $table->string('extension')->index()->nullable();
            $table->string('filename')->unique()->nullable();
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
        Schema::dropIfExists('assets');
    }
}
