<?php

namespace Tests\Feature\Http\Controllers\Attributes;

use Tests\Feature\FeatureCase;

/**
 * @group feature
 */
class AttributeGroupControllerTest extends FeatureCase
{
    public function test_can_list_all_attribute_groups()
    {
        $user = $this->admin();

        // dd($user->hasRole('admin'));
        $response = $this->actingAs($user)->json('GET', 'attribute-groups');

        dd($response);
        // dd($response);
    }
}
