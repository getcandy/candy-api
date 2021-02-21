<?php

namespace Tests\Unit\Products\Actions\Versioning;

use Tests\TestCase;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use GetCandy\Api\Core\Products\Models\ProductAssociation;
use GetCandy\Api\Core\Associations\Models\AssociationGroup;
use GetCandy\Api\Core\Products\Actions\Versioning\VersionProductAssociations;

/**
 * @group versionings
 */
class VersionProductAssociationsTest extends TestCase
{
    public function test_can_version_product_associations()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $group = factory(AssociationGroup::class)->create();

        factory(Product::class, 15)->create()->each(function ($p) use ($group, $product) {
            $assoc = new ProductAssociation;
            $assoc->group()->associate($group);
            $assoc->association()->associate($p);
            $assoc->parent()->associate($product);
            $assoc->save();
        });

        $version = (new CreateVersion)->actingAs($user)->run([
            'model' => $product
        ]);

        (new VersionProductAssociations)->actingAs($user)->run([
            'version' => $version,
            'model' => $product,
        ]);

        foreach ($product->associations as $assoc) {
            $versionedAssoc = $version->relations->first(function ($relation) use ($assoc) {
                return $relation->versionable_id == $assoc->id && $relation->versionable_type == get_class($assoc);
            });
            foreach ($versionedAssoc->model_data as $attribute => $value) {
                $this->assertEquals($assoc->getAttributes()[$attribute], $value);
            }
        }
    }
}
