<?php

namespace Tests\Feature\Actions\Languages;

use Tests\Feature\FeatureCase;

/**
 * @group languages
 */
class FetchLanguagesTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('GET', 'languages');

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/languages', 'get');
    }
}
