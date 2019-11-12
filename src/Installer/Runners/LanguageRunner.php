<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Console\Command;

class LanguageRunner implements InstallRunnerContract
{
    protected $command;

    protected $available;

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

        $chosenLangs = $this->command->choice(
            'Which Languages should we install? (seperate choices by a comma, you can always add more later)',
            $this->availableLanguages->mapWithKeys(function ($lang, $key) {
                return [$key => $lang['name']];
            })->toArray(),
            null,
            null,
            true
        );

        $languages = $this->availableLanguages->filter(function ($lang, $key) use ($chosenLangs) {
            return in_array($key, $chosenLangs);
        })->map(function ($lang, $key) use ($chosenLangs) {
            $lang['default'] = $chosenLangs[0] == $key;
            $lang['created_at'] = now();
            $lang['updated_at'] = now();
            return $lang;
        });

        DB::table('languages')->insert($languages->toArray());
    }
}
