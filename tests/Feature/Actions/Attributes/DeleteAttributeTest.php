<?php

namespace Tests\Feature\Actions\Attributes;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use Tests\Feature\FeatureCase;

/**
 * @group attributes
 */
class DeleteAttributeTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $attribute = factory(Attribute::class)->create([
            'system' => false,
        ]);

        $response = $this->actingAs($user)->json('DELETE', "attributes/{$attribute->encoded_id}");

        $response->assertStatus(204);
        $this->assertResponseValid($response, '/attributes/{attributeId}', 'delete');
    }
}
