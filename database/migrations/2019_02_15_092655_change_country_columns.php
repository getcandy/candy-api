<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCountryColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get our countries.
        $countries = json_decode(json_encode(DB::table('countries')->get()->toArray()), true);

        foreach ($countries as $index => $country) {
            $nameJson = json_decode($country['name'], true);
            $countries[$index]['name'] = $nameJson['en'];
        }

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
        });

        foreach ($countries as $country) {
            DB::table('countries')->where('id', $country['id'])->update([
                'name' => $country['name'],
            ]);
        }
    }

    public function down()
    {
        // This is a one way migration...
    }
}
