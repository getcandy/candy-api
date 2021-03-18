<?php

namespace Tests\Unit\Languages\Actions;

use GetCandy\Api\Core\Languages\Actions\FetchEnabledLanguageByCode;
use GetCandy\Api\Core\Languages\Models\Language;
use Tests\TestCase;

/**
 * @group languages
 */
class FetchEnabledLanguageByCodeTest extends TestCase
{
    public function test_can_fetch_languages()
    {
        $user = $this->admin();

        $languageA = factory(Language::class)->create([
            'enabled' => false,
            'code' => 'gba',
        ]);
        $languageB = factory(Language::class)->create([
            'enabled' => true,
            'code' => 'sk',
        ]);
        $languageC = factory(Language::class)->create([
            'enabled' => false,
            'code' => 'dk',
        ]);

        $this->assertFalse($languageA->enabled);
        $this->assertTrue($languageB->enabled);
        $this->assertFalse($languageC->enabled);

        $language = FetchEnabledLanguageByCode::run([
            'code' => $languageB->code,
        ]);

        $this->assertEquals($languageB->id, $language->id);

        $language = FetchEnabledLanguageByCode::run([
            'code' => $languageC->code,
        ]);

        $this->assertNull($language);
    }
}
