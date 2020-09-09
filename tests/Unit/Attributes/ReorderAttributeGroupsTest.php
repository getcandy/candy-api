<?php

namespace Tests\Unit\Attributes\Actions;

use GetCandy\Api\Core\Attributes\Actions\ReorderAttributeGroups;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\TestCase;

/**
 * @group attributes
 */
class ReorderAttributeGroupsTest extends TestCase
{
    public function test_can_create_bare_minimum_attribute()
    {
        $user = $this->admin();

        $groupOne = factory(AttributeGroup::class)->create([
            'position' => 1,
        ]);
        $groupTwo = factory(AttributeGroup::class)->create([
            'position' => 2,
        ]);
        $groupThree = factory(AttributeGroup::class)->create([
            'position' => 3,
        ]);

        $groups = collect([$groupOne, $groupTwo, $groupThree]);

        $positions = $groups->map(function ($group) {
            $pos = $group->position;

            return [
                'id' => $group->encoded_id,
                'position' => $pos + 1,
            ];
        });

        (new ReorderAttributeGroups)->actingAs($user)->run([
            'ordering' => $positions->toArray(),
        ]);

        $this->assertEquals(2, $groupOne->refresh()->position);
        $this->assertEquals(3, $groupTwo->refresh()->position);
        $this->assertEquals(4, $groupThree->refresh()->position);
    }
}
