<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportExportsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $userIdColType = Schema::getColumnType('users', 'id');
            if ($userIdColType == 'integer') {
                $table->unsignedInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users');
            } else {
                $table->foreignId('user_id')->constrained();
            }
            $table->string('report')->index();
            $table->string('filename')->nullable();
            $table->string('path')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('report_exports');
    }
}
