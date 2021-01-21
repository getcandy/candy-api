<?php

namespace Tests\Feature\Actions\Products;

use GetCandy\Api\Core\Products\Models\ProductFamily;
use Tests\Feature\FeatureCase;

/**
 * @group product-families
 */
class UpdateProductFamilyTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $productfamily = factory(ProductFamily::class)->create();

        $response = $this->actingAs($user)->json('put', "product-families/{$productfamily->encoded_id}", [
            'name' => 'Foo bar',
        ]);

        $data = json_decode($response->content());

        $this->assertEquals('Foo bar', $data->data->name);

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/product-families/{productFamilyId}', 'put');
    }
}
