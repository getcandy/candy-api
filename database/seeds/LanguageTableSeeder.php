<?php

namespace Seeds;

use GetCandy\Api\Core\Languages\Models\Language;
use Illuminate\Database\Seeder;

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
            'code' => 'en',
            'name' => 'English',
            'default' => true,
        ]);

        Language::forceCreate([
            'code' => 'fr',
            'name' => 'Français',
            'default' => false,
        ]);
    }
}
