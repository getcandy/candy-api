<?php

namespace Tests\Feature\Actions\Customers;

use Tests\Feature\FeatureCase;

/**
 * @group customers
 */
class FetchCustomersTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('GET', 'customers');

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/customers', 'get');
    }
}
