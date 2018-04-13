<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transforms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('handle')->unique();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('constraint')->nullable();
            $table->integer('quality')->default(100);
            $table->string('format')->default('jpg');
            $table->enum('mode', ['fit', 'fit-crop', 'crop', 'stretch'])->default('fit-crop');
            $table->enum('position', [
                'top-left',
                'top-center',
                'top-right',
                'center-left',
                'center-center',
                'center-right',
                'bottom-left',
                'bottom-center',
                'bottom-right',
            ])->default('center-center');
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
        Schema::dropIfExists('transforms');
    }
}
