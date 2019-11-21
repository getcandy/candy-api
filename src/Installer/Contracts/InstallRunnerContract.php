<?php

namespace GetCandy\Api\Installer\Contracts;

use Illuminate\Console\Command;

interface InstallRunnerContract
{
    public function run();

    public function after();

    public function onCommand(Command $command);
}
