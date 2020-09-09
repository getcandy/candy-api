<?php

namespace Tests\Feature\Actions\Attributes;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\Feature\FeatureCase;

/**
 * @group attributes
 */
class CreateAttributeTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class)->create();

        $response = $this->actingAs($user)->json('POST', 'attributes', [
            'attribute_group_id' => $group->encoded_id,
            'name' => [
                'en' => 'Test Attribute',
            ],
            'handle' => 'test-attribute-handle',
            'type' => 'text',
        ]);

        $response->assertStatus(201);
        $this->assertResponseValid($response, '/attributes', 'post');
    }
}
