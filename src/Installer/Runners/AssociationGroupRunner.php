<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Console\Command;

class AssociationGroupRunner extends AbstractRunner implements InstallRunnerContract
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function run()
    {
        // Are languages already installed?
        if (DB::table('association_groups')->count()) {
            return;
        }

        $this->command->info('Installing Association Groups');

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
