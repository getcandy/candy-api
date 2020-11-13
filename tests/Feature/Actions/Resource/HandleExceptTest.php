<?php

namespace Tests\Feature\Actions\Resource;

use GetCandy\Api\Core\Products\Models\Product;
use Tests\Feature\FeatureCase;

/**
 * @group resource
 */
class HandleExceptTest extends FeatureCase
{
    public function test_can_restrict_resource_response()
    {
        $user = $this->admin();

        factory(Product::class, 5)->create();

        $response = $this->actingAs($user)->json('GET', 'products?except=name');
        $response->assertStatus(200);

        $result = json_decode($response->getContent(), true);

        $this->assertArrayNotHasKey('name', $result['data'][0]);
    }
}
