<?php

namespace Tests\Unit\Attributes\Actions;

use GetCandy\Api\Core\Attributes\Actions\CreateAttribute;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\TestCase;

/**
 * @group attributes
 */
class AttachUserToCustomerTest extends TestCase
{
    public function test_can_create_bare_minimum_attribute()
    {
        $user = $this->admin();

        $attributeGroup = factory(AttributeGroup::class)->create();

        $attribute = (new CreateAttribute)->actingAs($user)->run([
            'attribute_group_id' => $attributeGroup->encoded_id,
            'name' => [
                'en' => 'Test Attribute',
            ],
            'type' => 'text',
            'handle' => 'test-attributes',
        ]);

        $this->assertNotNull($attribute->id);
        $this->assertInstanceOf(Attribute::class, $attribute);
    }
}
