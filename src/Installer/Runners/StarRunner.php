<?php

namespace GetCandy\Api\Installer\Runners;

use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class StarRunner extends AbstractRunner implements InstallRunnerContract
{
    public function run()
    {
        if ($this->command->confirm('Would you like to show some love by starring the repo?')) {
            $exec = PHP_OS_FAMILY === 'Windows' ? 'start' : 'open';

            exec("{$exec} https://github.com/getcandy/candy-api");

            $this->command->line("Thanks, you're awesome!");
        }
    }
}
