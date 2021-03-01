<?php

namespace Tests\Unit\Versioning\Actions;

use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use GetCandy\Api\Core\Versioning\Actions\VersionCategories;
use Tests\TestCase;

/**
 * @group versioning
 */
class VersionCategoriesTest extends TestCase
{
    public function test_can_version_model_categories()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        factory(Category::class, 2)->create()->each(function ($category) use ($product) {
            $product->categories()->attach($category);
        });

        $this->assertCount(2, $product->categories);

        $version = (new CreateVersion)->actingAs($user)->run([
            'model' => $product,
        ]);

        (new VersionCategories)->actingAs($user)->run([
            'version' => $version,
            'model' => $product,
        ]);

        $this->assertCount(2, $version->relations);

        foreach ($version->relations as $relation) {
            $this->assertEquals(Category::class, $relation->versionable_type);
        }

        // Make sure our version has the correct channels
        foreach ($product->categories as $category) {
            $versionable = $version->relations->first(function ($version) use ($category) {
                return $version->versionable_id == $category->id && $version->versionable_type === get_class($category);
            });
            $this->assertNotNull($version);
            $this->assertEquals($category->pivot->position, $versionable->model_data['position']);
        }
    }
}
