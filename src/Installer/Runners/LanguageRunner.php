<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Console\Command;

class LanguageRunner extends AbstractRunner implements InstallRunnerContract
{
    protected $command;

    protected $availableLanguages;

    public function __construct(Command $command)
    {
        $this->command = $command;

        $this->availableLanguages = collect([
            'gb' => [
                'lang' => 'en',
                'iso' => 'gb',
                'name' => 'English',
                'default' => true,
            ],
        ]);
    }

    public function run()
    {
        // Are languages already installed?
        if (DB::table('languages')->count()) {
            return;
        }

        $languages = $this->availableLanguages->map(function ($lang, $key) {
            $lang['default'] = $key == 'gb';
            $lang['created_at'] = now();
            $lang['updated_at'] = now();

            return $lang;
        });

        DB::table('languages')->insert($languages->toArray());
    }
}
