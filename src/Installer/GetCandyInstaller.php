<?php

namespace GetCandy\Api\Installer;

use Illuminate\Console\Command;
use GetCandy\Api\Installer\Runners\PreflightRunner;
use GetCandy\Api\Installer\Runners\LanguageRunner;
use GetCandy\Api\Installer\Runners\TaxRunner;
use GetCandy\Api\Installer\Runners\AttributeRunner;

class GetCandyInstaller
{
    protected $command;

    /**
     * Sets the command instance for running the installer
     *
     * @param Command $command
     * @return self
     */
    public function onCommand(Command $command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * Run the installer
     *
     * @return void
     */
    public function run()
    {
        if (!$this->command) {
            throw new \Exception('You must attach a command instance to the installer');
        }
        $this->runPreflight();
        $this->command->call('migrate');
        $this->installLanuages();
        $this->installTaxes();
        $this->installAttributes();
    }

    /**
     * Run the preflight command
     *
     * @return void
     */
    protected function runPreflight()
    {
        $this->command->info('Running preflight');
        (new PreflightRunner)->run();
    }

    /**
     * Run the language installer
     *
     * @return void
     */
    protected function installLanuages()
    {
        (new LanguageRunner($this->command))->run();
    }

    /**
     * Run the language installer
     *
     * @return void
     */
    protected function installTaxes()
    {
        (new TaxRunner($this->command))->run();
    }

    protected function installAttributes()
    {
        (new AttributeRunner)->run();
    }
}
