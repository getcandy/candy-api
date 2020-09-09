<?php

namespace Tests\Feature\Actions\Languages;

use GetCandy\Api\Core\Languages\Models\Language;
use Tests\Feature\FeatureCase;

/**
 * @group languages
 */
class DeleteLanguageTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $language = factory(Language::class)->create();

        $response = $this->actingAs($user)->json('delete', "languages/{$language->encoded_id}");

        $response->assertStatus(204);

        $this->assertResponseValid($response, '/languages/{languageId}', 'delete');
    }
}
