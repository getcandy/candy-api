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
    public function test_can_attach_user_to_customer_record()
    {
        $user = $this->admin();

        $languageA = factory(Language::class)->create([
            'enabled' => false,
            'iso' => 'gba',
        ]);
        $languageB = factory(Language::class)->create([
            'enabled' => true,
            'iso' => 'sk',
        ]);
        $languageC = factory(Language::class)->create([
            'enabled' => false,
            'iso' => 'dk',
        ]);

        $this->assertFalse($languageA->enabled);
        $this->assertTrue($languageB->enabled);
        $this->assertFalse($languageC->enabled);

        $language = FetchEnabledLanguageByCode::run([
            'code' => $languageB->iso,
        ]);

        $this->assertEquals($languageB->id, $language->id);

        $language = FetchEnabledLanguageByCode::run([
            'code' => $languageC->iso,
        ]);

        $this->assertNull($language);
    }
}
