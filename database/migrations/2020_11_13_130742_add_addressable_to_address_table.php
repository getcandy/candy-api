<?php

use GetCandy\Api\Core\Addresses\Models\Address;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class AddAddressableToAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $userModel = GetCandy::getUserModel();
        $addresses = Address::get();

        Schema::dropIfExists('addresses');

        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('addressable');
            $table->string('salutation')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('company_name')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('address')->nullable();
            $table->string('address_two')->nullable();
            $table->string('address_three')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->integer('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->boolean('shipping')->default(0);
            $table->boolean('billing')->default(0);
            $table->boolean('default')->default(false);
            $table->text('delivery_instructions')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        foreach ($addresses as $address) {
            $addressToCreate = Arr::except(array_merge(
                $address->toArray(),
                [
                    'addressable_type' => $userModel,
                    'addressable_id' => $address->user_id,
                ]
            ), ['user_id']);

            Address::forceCreate($addressToCreate);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropMorphs('addressable');
        });
    }
}
