<?php

namespace Tests\Feature\Actions\Attributes;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\Feature\FeatureCase;

/**
 * @group attributes
 */
class FetchAttributeTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class)->create();
        $group->attributes()->save(factory(Attribute::class)->make());
        $attribute = $group->attributes->first();

        $response = $this->actingAs($user)->json('GET', "attributes/{$attribute->encoded_id}");
        $response->assertStatus(200);
        $this->assertResponseValid($response, '/attributes/{attributeId}', 'get');
    }
}
