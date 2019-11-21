<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Console\Command;

class CustomerGroupRunner extends AbstractRunner implements InstallRunnerContract
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function run()
    {
        if (DB::table('customer_groups')->count()) {
            return;
        }

        DB::table('customer_groups')->insert([
            [
                'name' => 'Retail',
                'handle' => 'retail',
                'default' => true,
                'system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Guest',
                'handle' => 'guest',
                'default' => false,
                'system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
