<?php

namespace Tests\Unit\Languages\Actions;

use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use GetCandy\Api\Core\Languages\Models\Language;
use Tests\TestCase;

/**
 * @group languages
 */
class FetchDefaultLanguageTest extends TestCase
{
    public function test_can_attach_user_to_customer_record()
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

        $this->assertFalse($languageA->default);
        $this->assertTrue($languageB->default);
        $this->assertFalse($languageC->default);

        $language = FetchDefaultLanguage::run();

        $this->assertEquals($languageB->id, $language->id);
    }
}
