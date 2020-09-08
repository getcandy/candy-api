<?php

namespace Tests\Feature\Actions\Languages;

use Tests\Feature\FeatureCase;

/**
 * @group languages
 */
class CreateLanguageTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('POST', 'languages', [
            'lang' => 'en',
            'iso' => 'gba',
            'name' => 'English',
            'default' => 1,
            'enabled' => 1,
        ]);

        $response->assertStatus(201);

        $this->assertResponseValid($response, '/languages', 'post');
    }
}
