<?php

namespace Seeds;

use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Languages\Models\Language;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Language::forceCreate([
            'lang' => 'en',
            'iso' => 'gb',
            'name' => 'English',
            'default' => true,
        ]);

        Language::forceCreate([
            'lang' => 'fr',
            'iso' => 'fr',
            'name' => 'Français',
            'default' => false,
        ]);
    }
}
