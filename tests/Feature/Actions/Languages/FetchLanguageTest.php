<?php

namespace Tests\Feature\Actions\Languages;

use GetCandy\Api\Core\Languages\Models\Language;
use Tests\Feature\FeatureCase;

/**
 * @group languages
 */
class FetchLanguageTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $language = Language::first();

        $response = $this->actingAs($user)->json('GET', "languages/{$language->encoded_id}");

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/languages/{languageId}', 'get');
    }
}
