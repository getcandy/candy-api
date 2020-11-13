<?php

use GetCandy\Api\Core\Addresses\Models\Address;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        Schema::table('addresses', function (Blueprint $table) {
            Schema::disableForeignKeyConstraints();
            $table->dropForeign('addresses_user_id_foreign');
            $table->dropColumn('user_id');
            Schema::enableForeignKeyConstraints();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->string("addressable_type")->after('id')->default('');
            $table->unsignedBigInteger("addressable_id")->after('id')->default('');
            $table->index(["addressable_type", "addressable_id"]);
        });

        foreach ($addresses as $address) {
            Address::find($address->id)->update([
                'addressable_type' => $userModel,
                'addressable_id' => $address->user_id,
            ]);
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
