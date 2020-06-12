<?php

namespace GetCandy\Api\Installer\Runners;

use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Console\Command;

abstract class AbstractRunner implements InstallRunnerContract
{
    /**
     * The instance of the command.
     *
     * @var \Illuminate\Console\Command
     */
    protected $command;

    /**
     * Runs after the runner has run.
     *
     * @return void
     */
    public function after()
    {
    }

    /**
     * Sets the command instance for running the installer.
     *
     * @param  \Illuminate\Console\Command  $command
     * @return $this
     */
    public function onCommand(Command $command)
    {
        $this->command = $command;

        return $this;
    }
}
