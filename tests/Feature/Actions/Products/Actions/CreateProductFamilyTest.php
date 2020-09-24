<?php

namespace Tests\Feature\Actions\Products;

use Tests\Feature\FeatureCase;

/**
 * @group product-families
 */
class CreateProductFamilyTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('post', 'product-families', [
            'name' => 'Foo bar',
        ]);

        $response->assertStatus(201);

        $this->assertResponseValid($response, '/product-families', 'post');
    }

    public function test_validation_works_for_fields()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('post', 'product-families', [
            'name' => 'Foo bar',
        ]);

        $response->assertStatus(201);

        $response = $this->actingAs($user)->json('post', 'product-families', [
            'name' => 'Foo bar',
        ]);

        $response->assertStatus(422);

        $this->assertResponseValid($response, '/product-families', 'post');
    }
}
