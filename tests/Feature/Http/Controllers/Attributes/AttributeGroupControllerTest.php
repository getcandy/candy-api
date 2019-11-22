<?php

namespace Tests\Feature\Http\Controllers\Attributes;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\Feature\FeatureCase;

/**
 * @group feature
 */
class AttributeGroupControllerTest extends FeatureCase
{
    public function test_can_list_all_attribute_groups()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('GET', 'attribute-groups');

        $structure = [
            'data' => [0, 1],
            'meta' => [
                'pagination' => [
                    'total',
                    'count',
                    'per_page',
                    'current_page',
                    'total_pages',
                ],
            ],
        ];

        $response->assertJsonStructure($structure);
    }

    public function test_can_get_attribute_group_by_id()
    {
        $user = $this->admin();

        // Get an attribute group
        $group = AttributeGroup::first();

        $response = $this->actingAs($user)->json('GET', "attribute-groups/{$group->encodedId()}");

        $structure = [
            'data' => [
                'id',
                'name',
                'handle',
                'position',
            ],
            'meta',
        ];

        $response->assertJsonStructure($structure);

        $response->assertJson([
            'data' => [
                'id' => $group->encodedId(),
            ],
        ]);
    }

    public function test_can_can_create_attribute_group()
    {
        $user = $this->admin();

        // Get an attribute group
        $group = AttributeGroup::first();

        $response = $this->actingAs($user)->json('POST', 'attribute-groups', [
            'name' => [
                'en' => 'Test Group',
            ],
            'handle' => 'test-group',
            'position' => 3,
        ]);

        $structure = [
            'data' => [
                'id',
                'name',
                'handle',
                'position',
            ],
            'meta',
        ];

        $response->assertJsonStructure($structure);

        $response->assertJson([
            'data' => [
                'name' => [
                    'en' => 'Test Group',
                ],
                'handle' => 'test-group',
            ],
        ]);
    }

    public function test_can_update_attribute_group()
    {
        $user = $this->admin();

        // Get an attribute group
        $group = AttributeGroup::first();

        $response = $this->actingAs($user)->json('PUT', "attribute-groups/{$group->encodedId()}", [
            'name' => [
                'en' => 'Updated Name',
            ],
            'handle' => 'updated-handle',
            'position' => 9999,
        ]);

        $structure = [
            'data' => [
                'id',
                'name',
                'handle',
                'position',
            ],
            'meta',
        ];

        $response->assertJsonStructure($structure);

        $response->assertJson([
            'data' => [
                'name' => [
                    'en' => 'Updated Name',
                ],
                'handle' => 'updated-handle',
                'position' => 9999,
            ],
        ]);
    }

    /**
     * @group test
     */
    public function test_can_reorder_attribute_groups()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('GET', 'attribute-groups');

        $marketing = AttributeGroup::whereHandle('marketing')->first();
        $seo = AttributeGroup::whereHandle('seo')->first();

        $response->assertJson([
            'data' => [
                [
                    'handle' => 'marketing',
                    'position' => 1,
                ],
                [
                    'handle' => 'seo',
                    'position' => 2,
                ],
            ],
        ]);

        $this->actingAs($user)->json('PUT', 'attribute-groups/order', [
            'groups' => [
                $marketing->encodedId() => 2,
                $seo->encodedId() => 1,
            ],
        ])->assertStatus(204);

        $response = $this->actingAs($user)->json('GET', 'attribute-groups');

        $response->assertJson([
            'data' => [
                [
                    'handle' => 'seo',
                    'position' => 1,
                ],
                [
                    'handle' => 'marketing',
                    'position' => 2,
                ],
            ],
        ]);
    }

    public function test_can_delete_attribute_group()
    {
        $user = $this->admin();

        // Get an attribute group
        $group = AttributeGroup::first();

        $response = $this->actingAs($user)->json('DELETE', "attribute-groups/{$group->encodedId()}");

        $response->assertStatus(204);

        $response = $this->actingAs($user)->json('DELETE', "attribute-groups/{$group->encodedId()}");

        $response->assertStatus(404);
    }
}
