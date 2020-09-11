<?php

namespace Tests\Unit\Channels\Actions;

use GetCandy\Api\Core\Attributes\Actions\AttachModelToAttributes;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use Tests\TestCase;

/**
 * @group attributes
 */
class AttachModelToAttributesTest extends TestCase
{
    public function test_model_can_be_attached_to_attributes()
    {
        $attributeGroup = factory(AttributeGroup::class)->create();
        $attributeGroup->attributes()->saveMany(
            factory(Attribute::class, 3)->make()
        );

        $productFamily = factory(ProductFamily::class)->create();

        $this->assertCount(0, $productFamily->attributes);

        (new AttachModelToAttributes)->actingAs($this->admin())->run([
            'model' => $productFamily,
            'attribute_ids' => $attributeGroup->attributes->map(function ($att) {
                return $att->encoded_id;
            })->toArray(),
        ]);

        $this->assertCount($attributeGroup->attributes->count(), $productFamily->load('attributes')->attributes);
    }
}
