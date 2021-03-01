<?php

namespace Tests\Unit\Versioning\Actions;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use GetCandy\Api\Core\Versioning\Actions\VersionCustomerGroups;
use Tests\TestCase;

/**
 * @group versioning
 */
class VersionCustomerGroupsTest extends TestCase
{
    public function test_can_create_a_version_of_a_model()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        factory(CustomerGroup::class, 2)->create()->each(function ($group) use ($product) {
            $product->customerGroups()->attach($group->id, [
                'purchasable' => true,
                'visible' => true,
            ]);
        });

        $this->assertCount(2, $product->customerGroups);

        $version = (new CreateVersion)->actingAs($user)->run([
            'model' => $product,
        ]);

        (new VersionCustomerGroups)->actingAs($user)->run([
            'version' => $version,
            'model' => $product,
        ]);

        $this->assertCount(2, $version->relations);

        foreach ($version->relations as $relation) {
            $this->assertEquals(CustomerGroup::class, $relation->versionable_type);
        }

        // Make sure our version has the correct channels
        foreach ($product->customerGroups as $group) {
            $versionable = $version->relations->first(function ($version) use ($group) {
                return $version->versionable_id == $group->id && $version->versionable_type === get_class($group);
            });
            $this->assertNotNull($version);
            $this->assertEquals($group->pivot->purchasable, $versionable->model_data['purchasable']);
            $this->assertEquals($group->pivot->visible, $versionable->model_data['visible']);
        }
    }
}
