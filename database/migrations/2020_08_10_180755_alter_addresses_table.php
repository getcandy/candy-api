<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAddressesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->renameColumn('zip', 'postal_code');
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('county');
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('salutation')->after('user_id')->nullable();
            $table->text('delivery_instructions')->after('default')->nullable();
            $table->integer('country_id')->unsigned()->after('postal_code')->nullable();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->string('phone')->after('lastname')->nullable()->index();
            $table->string('email')->after('lastname')->nullable()->index();
            $table->string('company_name')->after('lastname')->nullable()->index();
            $table->timestamp('last_used_at')->after('default')->nullable();
            $table->json('meta')->nullable();
        });

        $addresses = DB::table('addresses')->get();
        $countries = DB::table('countries')->get();

        foreach ($addresses as $address) {
            $country = $countries->first(function ($country) use ($address) {
                return $country->name === $address->country;
            });
            if (! $country) {
                continue;
            }
            DB::table('addresses')->whereId($address->id)->update([
                'country_id' => $country->id,
            ]);
        }

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists(config('activitylog.table_name'));
    }
}
