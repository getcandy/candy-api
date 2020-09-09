<?php

namespace Tests\Feature\Actions\Attributes;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use Tests\Feature\FeatureCase;

/**
 * @group attributes
 */
class UpdateAttributeTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $attribute = factory(Attribute::class)->create();

        $response = $this->actingAs($user)->json('PUT', "attributes/{$attribute->encoded_id}", [
            'name' => [
                'en' => 'Test Attribute'
            ],
            'handle' => 'test-attribute-handle',
            'type' => 'text',
        ]);

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/attributes/{attributeId}', 'put');
    }
}
