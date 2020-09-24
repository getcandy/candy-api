<?php

namespace Tests\Unit\Languages\Actions;

use GetCandy\Api\Core\Languages\Actions\FetchLanguages;
use GetCandy\Api\Core\Languages\Models\Language;
use Tests\TestCase;

/**
 * @group languages
 */
class FetchLangugesTest extends TestCase
{
    public function test_can_fetch_all_langauges()
    {
        $user = $this->admin();

        $languageA = factory(Language::class)->create([
            'default' => false,
        ]);
        $languageB = factory(Language::class)->create([
            'default' => true,
        ]);
        $languageC = factory(Language::class)->create([
            'default' => false,
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
        ]);
        $languageB = factory(Language::class)->create([
            'default' => true,
        ]);
        $languageC = factory(Language::class)->create([
            'default' => false,
        ]);

        $languages = FetchLanguages::run();

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $languages);
    }

    public function test_can_search_languages()
    {
        $user = $this->admin();

        $languageA = factory(Language::class)->create([
            'lang' => 'en',
            'iso' => 'gba',
            'enabled' => true,
        ]);
        $languageB = factory(Language::class)->create([
            'lang' => 'sk',
            'iso' => 'sk',
            'enabled' => true,
        ]);
        $languageC = factory(Language::class)->create([
            'lang' => 'dk',
            'iso' => 'dk',
            'enabled' => true,
        ]);

        $languages = FetchLanguages::run([
            'paginate' => false,
            'search' => [
                'lang' => ['en', 'sk'],
            ],
        ]);

        $test = Language::whereIn('lang', ['en', 'sk'])->count();

        $this->assertEquals($test, $languages->count());
    }
}
