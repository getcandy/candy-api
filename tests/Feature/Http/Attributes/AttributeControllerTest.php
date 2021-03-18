<?php

namespace Tests\Feature\Http\Controllers\Attributes;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Tests\Feature\FeatureCase;

/**
 * @group attributes
 */
class AttributeControllerTest extends FeatureCase
{
    protected function createAttribute()
    {
        $group = AttributeGroup::forceCreate([
            'name' => [
                'en' => 'Test attribute group',
            ],
            'handle' => 'test-attribute-group',
            'position' => 1,
        ]);

        return Attribute::forceCreate([
            'name' => [
                'en' => 'Test attribute',
            ],
            'group_id' => $group->id,
            'handle' => 'test-attribute',
            'position' => 1,
        ]);
    }

    public function test_can_list_all_attributes()
    {
        $user = $this->admin();

        $this->createAttribute();

        $response = $this->actingAs($user)->json('GET', 'attributes');

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/attributes');
    }

    public function test_can_show_a_attribute_by_id()
    {
        $user = $this->admin();
        $this->createAttribute();
        $attributeId = Attribute::first()->encodedId();
        $response = $this->actingAs($user)->json('GET', "attributes/{$attributeId}");
        $response->assertStatus(200);

        $this->assertResponseValid($response, '/attributes/{attributeId}');
    }

    public function test_missing_shows_appropriate_response()
    {
        $user = $this->admin();
        $response = $this->actingAs($user)->json('GET', 'attributes/9999');

        $response->assertStatus(404);

        $this->assertResponseValid($response, '/attributes/{attributeId}');
    }

    public function test_can_update_an_attribute()
    {
        $user = $this->admin();
        $this->createAttribute();
        $attribute = Attribute::first();
        $attributeId = $attribute->encodedId();
        $response = $this->actingAs($user)->json('PUT', "attributes/{$attributeId}", [
            'name' => [
                'en' => 'Updated test attribute',
            ],
        ]);
        $response->assertStatus(200);

        $name = $attribute->refresh()->name;
        $this->assertEquals('Updated test attribute', $name['en']);

        $this->assertResponseValid($response, '/attributes/{attributeId}', 'put');
    }

    public function test_validation_works_on_update()
    {
        $user = $this->admin();
        $this->createAttribute();

        $attribute = Attribute::first();
        $attributeId = $attribute->encodedId();
        $response = $this->actingAs($user)->json('PUT', "attributes/{$attributeId}");
        $response->assertStatus(422);
        $this->assertResponseValid($response, '/attributes/{attributeId}', 'put');
    }

    public function test_validation_works_on_create()
    {
        $user = $this->admin();
        $response = $this->actingAs($user)->json('POST', 'attributes');
        $response->assertStatus(422);
        $this->assertResponseValid($response, '/attributes', 'post');
    }

    public function test_unique_handle_validation_works_on_create()
    {
        $user = $this->admin();
        $this->createAttribute();
        $groupId = AttributeGroup::first()->encodedId();
        $response = $this->actingAs($user)->json('POST', 'attributes', [
            'name' => [
                'en' => 'Another attribute',
            ],
            'handle' => 'test-attribute',
            'group_id' => $groupId,
        ]);
        $response->assertStatus(422)->assertJson([
            'handle' => [
                'The handle has already been taken.',
            ],
        ]);
    }

    public function test_can_create_attribute()
    {
        $user = $this->admin();
        $group = AttributeGroup::forceCreate([
            'name' => [
                'en' => 'Test attribute group',
            ],
            'handle' => 'test-attribute-group',
            'position' => 1,
        ]);

        $groupId = AttributeGroup::first()->encodedId();
        $response = $this->actingAs($user)->json('POST', 'attributes', [
            'name' => [
                'en' => 'Another attribute',
            ],
            'handle' => 'another-attribute',
            'group_id' => $groupId,
        ]);

        $response->assertStatus(201);
        $this->assertResponseValid($response, '/attributes', 'post');
    }
}
