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
            'code' => 'gbr',
            'name' => 'English',
            'default' => 1,
            'enabled' => 1,
        ]);

        $response->assertStatus(201);

        $this->assertResponseValid($response, '/languages', 'post');
    }

    public function test_validation_works_on_code_field()
    {
        $user = $this->admin();

        $codes = [
            '{}tg12}',
            '={}2ed]',
            '1234}',
            '1{@3{5}',
            'semicolon;',
            '1{@3{5}-',
        ];

        foreach ($codes as $code) {
            $response = $this->actingAs($user)->json('POST', 'languages', [
                'code' => $code,
                'name' => 'English',
                'default' => 1,
                'enabled' => 1,
            ]);

            $response->assertStatus(422);

            $this->assertResponseValid($response, '/languages', 'post');
        }
    }

    public function test_validation_allows_code_pattern()
    {
        $user = $this->admin();

        $codes = [
            'DE-DE-12',
            '2dw3e-2',
            'DEGB',
            '09-DE',
        ];

        foreach ($codes as $code) {
            $response = $this->actingAs($user)->json('POST', 'languages', [
                'code' => $code,
                'name' => 'English',
                'default' => 1,
                'enabled' => 1,
            ]);

            $response->assertStatus(201);

            $this->assertResponseValid($response, '/languages', 'post');
        }
    }
}
