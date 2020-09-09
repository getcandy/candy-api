<?php

namespace Tests\Feature\Actions\Attributes;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\Feature\FeatureCase;

/**
 * @group attributes
 */
class FetchAttributeGroupTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class)->create();

        $response = $this->actingAs($user)->json('GET', "attribute-groups/{$group->encoded_id}");

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/attribute-groups/{attributeGroupId}', 'get');
    }
}
