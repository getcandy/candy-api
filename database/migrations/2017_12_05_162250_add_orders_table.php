<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('basket_id')->unsigned()->nullable();
            $table->foreign('basket_id')->references('id')->on('baskets');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->decimal('total', 10, 2);
            $table->decimal('vat', 10, 2);
            $table->decimal('shipping_total', 10, 2);
            $table->string('shipping_method')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('status')->default('open')->index();

            $table->text('notes')->nullable();

            $table->string('currency');

            $table->string('billing_phone')->nullable();
            $table->string('billing_firstname')->nullable();
            $table->string('billing_lastname')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_address_two')->nullable();
            $table->string('billing_address_three')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_county')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_zip')->nullable();

            $table->string('shipping_phone')->nullable();
            $table->string('shipping_firstname')->nullable();
            $table->string('shipping_lastname')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_address_two')->nullable();
            $table->string('shipping_address_three')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_county')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_zip')->nullable();

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
        Schema::dropIfExists('orders');
    }
}
