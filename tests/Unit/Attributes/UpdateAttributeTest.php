<?php

namespace Tests\Unit\Attributes\Actions;

use GetCandy\Api\Core\Attributes\Actions\UpdateAttribute;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\TestCase;

/**
 * @group attributes
 */
class UpdateAttributeTest extends TestCase
{
    public function test_can_update_attribute()
    {
        $user = $this->admin();

        $attribute = factory(Attribute::class)->create();
        $group = factory(AttributeGroup::class)->create();

        $attribute = (new UpdateAttribute)->actingAs($user)->run([
            'attribute_group_id' => $group->encoded_id,
            'encoded_id' => $attribute->encoded_id,
            'name' => [
                'en' => 'Foo bar',
            ],
            'handle' => 'HANDLE!',
        ]);

        $this->assertEquals(['en' => 'Foo bar'], $attribute->name);
        $this->assertEquals($attribute->handle, 'HANDLE!');
        $this->assertEquals($group->id, $attribute->attributeGroup->id);
    }
}
