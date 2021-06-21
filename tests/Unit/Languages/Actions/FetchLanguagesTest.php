<?php

namespace Tests\Unit\Languages\Actions;

use GetCandy\Api\Core\Languages\Actions\FetchLanguages;
use GetCandy\Api\Core\Languages\Models\Language;
use Tests\TestCase;

/**
 * @group languages
 */
class FetchLanguagesTest extends TestCase
{
    public function test_can_fetch_all_langauges()
    {
        $user = $this->admin();

        $languageA = factory(Language::class)->create([
            'default' => false,
            'code' => 'en-a',
        ]);
        $languageB = factory(Language::class)->create([
            'default' => true,
            'code' => 'en-b',
        ]);
        $languageC = factory(Language::class)->create([
            'default' => false,
            'code' => 'en-c',
        ]);

        $languages = FetchLanguages::run([
            'paginate' => false,
        ]);

        $this->assertCount(Language::count(), $languages);
    }

    public function test_can_paginate_languages()
    {
        $user = $this->admin();

        $languageA = factory(Language::class)->create([
            'default' => false,
            'code' => 'en-a',
        ]);
        $languageB = factory(Language::class)->create([
            'default' => true,
            'code' => 'en-b',
        ]);
        $languageC = factory(Language::class)->create([
            'default' => false,
            'code' => 'en-c',
        ]);

        $languages = FetchLanguages::run(['paginate' => true]);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $languages);
    }

    public function test_can_search_languages()
    {
        $user = $this->admin();

        $languageA = factory(Language::class)->create([
            'code' => 'gba',
            'enabled' => true,
        ]);
        $languageB = factory(Language::class)->create([
            'code' => 'sk',
            'enabled' => true,
        ]);
        $languageC = factory(Language::class)->create([
            'code' => 'dk',
            'enabled' => true,
        ]);
        $languages = FetchLanguages::run([
            'paginate' => false,
            'search' => [
                'code' => ['en', 'sk'],
            ],
        ]);

        $test = Language::whereIn('code', ['en', 'sk'])->count();

        $this->assertEquals($test, $languages->count());
    }
}
