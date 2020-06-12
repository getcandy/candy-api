<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class LanguageRunner extends AbstractRunner implements InstallRunnerContract
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $availableLanguages;

    public function __construct()
    {
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
