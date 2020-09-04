<?php

namespace Tests\Feature\Actions\Customers;

use Tests\Feature\FeatureCase;

/**
 * @group customers
 */
class CreateCustomerTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('POST', 'customers', [
            'firstname' => 'Foo',
            'lastname' => 'bar',
        ]);

        $response->assertStatus(201);

        $this->assertResponseValid($response, '/customers', 'post');
    }

    public function test_can_validate_request()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('POST', 'customers', [
            'user_id' => 123123123,
        ]);

        $response->assertStatus(422);
        $this->assertResponseValid($response, '/customers', 'post');
    }
}
