<?php

namespace Tests\Unit\Products\Factories;

use GetCandy\Api\Core\Products\Models\Product;
use Tests\TestCase;
use Versioning;

/**
 * @group versioning
 */
class ProductVersionerTest extends TestCase
{
    public function test_can_create_and_restore_a_version()
    {
        // $versioner = Versioning::with('products');

        // // Get a product and create a version
        // $product = Product::first();

        // $data = $product->attribute_data;
        // $data['name']['webstore']['en'] = 'Blue Dog';

        // $version = $versioner->create($product);

        // $product->attribute_data = $data;
        // $product->save();

        // // Make sure the name is changed...
        // $this->assertEquals('Blue Dog', $product->attribute('name'));

        // // Make sure the version has the original name.
        // $data = $version->model_data['attribute_data'];

        // $this->assertEquals('Red Dog', $data['name']['webstore']['en']);

        // // dd($version->relations);
        // $restored = $versioner->restore($version);

        // $this->assertEquals($restored->publishedParent->id, $product->id);

        // $this->assertEquals($product->customerGroups->count(), $restored->customerGroups->count());
    }
}
