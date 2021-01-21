<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentProviderUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_provider_users', function (Blueprint $table) {
            $table->id();

            $userIdColType = Schema::getColumnType('users', 'id');

            if ($userIdColType == 'integer') {
                $table->unsignedInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users');
            } else {
                $table->foreignId('user_id')->constrained();
            }

            $table->string('provider_id')->index();
            $table->string('provider')->index();
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
        Schema::table('reusable_payments', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
}
