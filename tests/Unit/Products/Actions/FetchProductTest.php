<?php

namespace Tests\Unit\Channels\Actions;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Actions\FetchProduct;

/**
 * @group products
 */
class FetchProductTest extends TestCase
{
    public function test_can_get_product_by_hashed_id()
    {
        $product = factory(Product::class)->create();

        $fetchedProduct = FetchProduct::run([
            'encoded_id' => $product->encoded_id,
        ]);

        dd($fetchedProduct);
        $this->assertEquals($product->id, $fetchedProduct->id);
    }
}
