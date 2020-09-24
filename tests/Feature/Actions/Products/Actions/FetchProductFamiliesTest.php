<?php

namespace Tests\Feature\Actions\Products;

use Tests\Feature\FeatureCase;

/**
 * @group product-families
 */
class FetchProductFamiliesTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('GET', 'product-families');

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/product-families', 'get');
    }
}
