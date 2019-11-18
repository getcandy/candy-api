<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use File;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class CountryRunner extends AbstractRunner implements InstallRunnerContract
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    protected function getCountries()
    {
        try {
            return collect(json_decode(File::get(
                __DIR__.'/../../../countries.json'
            ), true));
        } catch (FileNotFoundException $e) {
            return null;
        }
    }

    public function run()
    {
        // Are countries already installed?
        if (DB::table('countries')->count()) {
            return;
        }

        $this->command->info('Installing Countries');

        $countries = $this->getCountries();

        if (!$countries) {
            $this->command->error('Unable to install countries - JSON file missing');
            return;
        }

        DB::table('countries')->insert($countries->map(function ($country) {
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
        })->toArray());
    }
}
