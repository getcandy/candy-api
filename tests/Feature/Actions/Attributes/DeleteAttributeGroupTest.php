<?php

namespace Tests\Feature\Actions\Attributes;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\Feature\FeatureCase;

/**
 * @group attributes
 */
class DeleteAttributeGroupTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class)->create();

        $response = $this->actingAs($user)->json('DELETE', "attribute-groups/{$group->encoded_id}");

        $response->assertStatus(204);
        $this->assertResponseValid($response, '/attribute-groups/{attributeGroupId}', 'delete');
    }
}
