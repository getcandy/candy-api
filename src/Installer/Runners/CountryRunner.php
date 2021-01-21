<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use File;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class CountryRunner extends AbstractRunner implements InstallRunnerContract
{
    protected function getCountries()
    {
        try {
            return collect(json_decode(File::get(
                __DIR__.'/../../../countries.json'
            ), true));
        } catch (FileNotFoundException $e) {
            return;
        }
    }

    public function run()
    {
        // Are countries already installed?
        if (DB::table('countries')->count()) {
            return;
        }

        $countries = $this->getCountries();

        if (! $countries) {
            $this->command->error('Unable to install countries - JSON file missing');

            return;
        }

        $countries->map(function ($country) {
            return [
                'name' => $country['name']['common'],
                'iso_a_2' => $country['cca2'],
                'iso_a_3' => $country['cca3'],
                'iso_numeric' => $country['ccn3'],
                'region' => $country['region'],
                'sub_region' => $country['subregion'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->chunk(50)->each(function ($rows) {
            DB::table('countries')->insert($rows->toArray());
        });

        $states = json_decode(File::get(__DIR__.'/../../../states.json'));

        foreach ($states as $state) {
            DB::table('states')->insert([
                'country_id' => $state->countryId,
                'name' => $state->name,
                'code' => $state->abbreviation,
            ]);
        }
    }
}
