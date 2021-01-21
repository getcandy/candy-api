<?php

namespace Tests\Feature\Actions\Languages;

use GetCandy\Api\Core\Products\Models\ProductFamily;
use Tests\Feature\FeatureCase;

/**
 * @group product-families
 */
class DeleteProductFamilyTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $productFamily = factory(ProductFamily::class)->create();

        $response = $this->actingAs($user)->json('delete', "product-families/{$productFamily->encoded_id}");

        $response->assertStatus(204);

        $this->assertResponseValid($response, '/product-families/{productFamilyId}', 'delete');
    }
}
