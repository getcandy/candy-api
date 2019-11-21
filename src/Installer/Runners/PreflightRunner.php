<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use GetCandy\Api\Installer\Events\PreflightCompletedEvent;
use Illuminate\Database\QueryException;

class PreflightRunner extends AbstractRunner implements InstallRunnerContract
{
    public function run()
    {
        $apiVersion = GetCandy::version();

        try {
            $dbVersions = collect(DB::select(DB::raw('SHOW VARIABLES LIKE "%version%";')));

            $dbVersion = $dbVersions->first(function ($version) {
                return $version->Variable_name == 'version';
            })->Value;
        } catch (QueryException $e) {
            $dbVersion = null;
        }

        event(new PreflightCompletedEvent([
            'api' => [
                'version' => $apiVersion,
                'success' => true,
            ],
            'database' => [
                'version' => $dbVersion,
                'connected' => $dbVersion ?? false,
                'success' => (bool) $dbVersion,
            ],
        ]));

        return $this;
    }

    public function after()
    {
        $this->command->call('migrate');
    }
}
