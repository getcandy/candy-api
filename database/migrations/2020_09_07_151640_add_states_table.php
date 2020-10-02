<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class AddStatesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (! Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('country_id')->unsigned();
                $table->foreign('country_id')->references('id')->on('countries');
                $table->string('name');
                $table->string('code');
            });
        }

        // Do we have countries in the database already? i.e. are we coming from an upgrade or install
        // If it's a fresh install we don't add states here...
        if (! DB::table('countries')->count()) {
            return true;
        }

        $states = json_decode(File::get(__DIR__.'/../../states.json'));

        foreach ($states as $state) {
            DB::table('states')->insert([
                'country_id' => $state->countryId,
                'name' => $state->name,
                'code' => $state->abbreviation,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('preferred');
            $table->dropColumn('enabled');
        });
    }
}
