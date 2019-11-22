<?php

namespace Tests\Unit\Http\Resources\Attributes;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Attributes\AttributeGroupResource;
use Tests\TestCase;

/**
 * @group resources
 */
class AttributeGroupResourceTest extends TestCase
{
    public function test_correct_data_is_returned_in_response()
    {
        $group = factory(AttributeGroup::class)->create();

        $resource = (new AttributeGroupResource($group))->jsonSerialize();

        $this->assertArraySubset([
            'id' => $group->encoded_id,
            'name' => $group->name,
            'handle' => $group->handle,
            'position' => $group->position,
        ], $resource);
    }

    public function test_attributes_relationship_is_not_loaded_by_default()
    {
        $group = factory(AttributeGroup::class, 1)->create()->each(function ($group) {
            $group->attributes()->save(
                factory(Attribute::class)->make()
            );
        })->first();

        $resource = (new AttributeGroupResource($group))->jsonSerialize();

        $this->assertArrayNotHasKey('attributes', $resource);
    }

    public function test_group_has_attributes_relationship()
    {
        $group = factory(AttributeGroup::class, 1)->create()->each(function ($group) {
            $group->attributes()->save(
                factory(Attribute::class)->make()
            );
        })->first();

        $group->load('attributes');

        $resource = (new AttributeGroupResource($group))->jsonSerialize();

        $this->assertInstanceOf(AttributeCollection::class, $resource["attributes"]);
    }
}
