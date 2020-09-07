<?php

namespace Tests\Feature\Actions\Attributes;

use Tests\Feature\FeatureCase;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;

/**
 * @group attributes
 */
class FetchAttributesTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class)->create();

        for ($i=0; $i < 10; $i++) {
            $group->attributes()->save(factory(Attribute::class)->make());
        }

        $response = $this->actingAs($user)->json('GET', 'attributes');

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/attributes', 'get');
    }

    public function test_can_paginate_results()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class)->create();

        for ($i=0; $i < 10; $i++) {
            $group->attributes()->save(factory(Attribute::class)->make());
        }

        $response = $this->actingAs($user)->json('GET', 'attributes', [
            'per_page' => 5,
        ]);

        $contents = json_decode($response->content());

        $this->assertCount(5, $contents->data);
    }

    public function test_can_return_all_records()
    {
        $user = $this->admin();
        $group = factory(AttributeGroup::class)->create();

        for ($i=0; $i < 100; $i++) {
            $group->attributes()->save(factory(Attribute::class)->make());
        }

        $response = $this->actingAs($user)->json('GET', 'attributes', [
            'paginate' => false,
        ]);

        $contents = json_decode($response->content());

        $this->assertCount(Attribute::count(), $contents->data);
    }
}
