<?php

namespace Tests\Unit\Attributes\Actions;

use GetCandy\Api\Core\Attributes\Actions\ReorderAttributes;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use Tests\TestCase;

/**
 * @group attributes
 */
class ReorderAttributesTest extends TestCase
{
    public function test_can_create_bare_minimum_attribute()
    {
        $user = $this->admin();

        $attributeOne = factory(Attribute::class)->create([
            'position' => 1,
        ]);
        $attributeTwo = factory(Attribute::class)->create([
            'position' => 2,
        ]);
        $attributeThree = factory(Attribute::class)->create([
            'position' => 3,
        ]);

        $attributes = collect([$attributeOne, $attributeTwo, $attributeThree]);

        $positions = $attributes->map(function ($attribute) {
            $pos = $attribute->position;

            return [
                'id' => $attribute->encoded_id,
                'position' => $pos + 1,
            ];
        });

        (new ReorderAttributes)->actingAs($user)->run([
            'ordering' => $positions->toArray(),
        ]);

        $this->assertEquals(2, $attributeOne->refresh()->position);
        $this->assertEquals(3, $attributeTwo->refresh()->position);
        $this->assertEquals(4, $attributeThree->refresh()->position);
    }
}
