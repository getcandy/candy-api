<?php

namespace Tests\Feature\Actions\Attributes;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\Feature\FeatureCase;

/**
 * @group attributes
 */
class CreateAttributeGroupTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class)->create();

        $response = $this->actingAs($user)->json('POST', "attribute-groups", [
            'name' => [
                'en' => 'Test Attribute Group'
            ],
            'handle' => 'test-attribute-group-handle',
        ]);

        $response->assertStatus(201);
        $this->assertResponseValid($response, '/attribute-groups', 'post');
    }
}
