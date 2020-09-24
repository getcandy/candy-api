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
        ]);
        $languageB = factory(Language::class)->create([
            'default' => true,
        ]);
        $languageC = factory(Language::class)->create([
            'default' => false,
        ]);

        $this->assertFalse($languageA->default);
        $this->assertTrue($languageB->default);
        $this->assertFalse($languageC->default);

        $language = FetchDefaultLanguage::run();

        $this->assertEquals($languageB->id, $language->id);
    }
}
