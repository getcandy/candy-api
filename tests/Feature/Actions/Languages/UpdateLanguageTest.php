<?php

namespace Tests\Feature\Actions\Languages;

use GetCandy\Api\Core\Languages\Models\Language;
use Tests\Feature\FeatureCase;

/**
 * @group languages
 */
class UpdateLanguageTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $language = factory(Language::class)->create();

        $response = $this->actingAs($user)->json('put', "languages/{$language->encoded_id}", [
            'lang' => 'en',
            'iso' => 'gba',
            'name' => 'English',
            'default' => 1,
            'enabled' => 1,
        ]);

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/languages/{languageId}', 'put');
    }
}
