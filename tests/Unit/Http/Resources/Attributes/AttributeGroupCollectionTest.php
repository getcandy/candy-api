<?php

namespace Tests\Unit\Http\Resources\Attributes;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Attributes\AttributeResource;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * @group resources
 */
class AttributeGroupCollectionTest extends TestCase
{
    public function test_collection_is_returned_in_response()
    {
        $groups = factory(AttributeGroup::class, 3)->create();
        $collection = (new AttributeCollection($groups))->jsonSerialize();
        $this->assertInstanceOf(Collection::class, $collection['data']);
    }

    public function test_correct_resource_is_used_in_response()
    {
        $groups = factory(AttributeGroup::class, 3)->create();

        $collection = (new AttributeCollection($groups))->jsonSerialize();

        $this->assertInstanceOf(AttributeResource::class, $collection['data']->first());
    }
}
