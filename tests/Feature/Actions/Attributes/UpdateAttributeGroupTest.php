<?php

namespace Tests\Feature\Actions\Attributes;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\Feature\FeatureCase;

/**
 * @group attributes
 */
class UpdateAttributeGroupTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $attribute = factory(AttributeGroup::class)->create();

        $response = $this->actingAs($user)->json('PUT', "attribute-groups/{$attribute->encoded_id}", [
            'name' => [
                'en' => 'Test Attribute'
            ],
            'handle' => 'test-attribute-handle',
        ]);

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/attribute-groups/{attributeGroupId}', 'put');
    }
}
