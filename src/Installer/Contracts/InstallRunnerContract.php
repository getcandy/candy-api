<?php

namespace GetCandy\Api\Installer\Contracts;

interface InstallRunnerContract
{
    public function run();

    public function after();
}
