<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use Elastica\Client;
use Elastica\Exception\Connection\HttpException;
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

        try {
            $elastica = new Client(config('getcandy.search.client_config.elastic'));

            $result = $elastica->request('/');

            $esVersion = $result->getData()['version']['number'];
        } catch (HttpException $e) {
            $esVersion = null;
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
            'elasticsearch' => [
                'version' => $esVersion,
                'connected' => $esVersion ?? false,
                'success' => $esVersion !== null
                    && version_compare($esVersion, '6.9', '<')
                    && version_compare($esVersion, '6.8', '>='),

            ],
        ]));

        return $this;
    }

    public function after()
    {
        $this->command->call('migrate');
    }
}
