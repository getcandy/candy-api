<?php

namespace GetCandy\Api\Installer\Runners;

use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

abstract class AbstractRunner implements InstallRunnerContract
{
    /**
     * Runs after the runner has run.
     *
     * @return void
     */
    public function after()
    {
    }
}
