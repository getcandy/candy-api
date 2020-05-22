<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class AssociationGroupRunner extends AbstractRunner implements InstallRunnerContract
{
    public function run()
    {
        // Are languages already installed?
        if (DB::table('association_groups')->count()) {
            return;
        }

        DB::table('association_groups')->insert([
            [
                'name' => 'Upsell',
                'handle' => 'upsell',
            ],
            [
                'name' => 'Cross-sell',
                'handle' => 'cross-sell',
            ],
            [
                'name' => 'Alternate',
                'handle' => 'alternate',
            ],
        ]);
    }
}
