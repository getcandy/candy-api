<?php

namespace Tests\Feature\Actions\Attributes;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\Feature\FeatureCase;

/**
 * @group attributes
 */
class FetchAttributeGroupsTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class, 100)->create();

        $response = $this->actingAs($user)->json('GET', 'attribute-groups');

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/attribute-groups', 'get');
    }

    public function test_can_paginate_results()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class, 100)->create();

        $response = $this->actingAs($user)->json('GET', 'attribute-groups', [
            'per_page' => 5,
        ]);

        $contents = json_decode($response->content());

        $this->assertCount(5, $contents->data);
    }

    public function test_can_return_all_records()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class, 100)->create();

        $response = $this->actingAs($user)->json('GET', 'attribute-groups', [
            'paginate' => false,
        ]);

        $contents = json_decode($response->content());

        $this->assertCount(AttributeGroup::count(), $contents->data);
    }
}
