<?php

namespace Tests\Unit\Versioning\Actions;

use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Assets\Models\AssetSource;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use GetCandy\Api\Core\Versioning\Actions\VersionAssets;
use Tests\TestCase;

/**
 * @group versionings
 */
class VersionAssetsTest extends TestCase
{
    public function test_can_version_model_routes()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        factory(AssetSource::class)->create()->each(function ($source) use ($product) {
            $source->assets()->createMany(
                factory(Asset::class, 2)->make()->toArray()
            );

            foreach ($source->assets as $asset) {
                $product->assets()->attach($asset->id, [
                    'primary' => 1,
                    'position' => 1,
                    'assetable_type' => Product::class,
                ]);
            }
        });

        $version = (new CreateVersion)->actingAs($user)->run([
            'model' => $product,
        ]);

        (new VersionAssets)->actingAs($user)->run([
            'version' => $version,
            'model' => $product,
        ]);

        $this->assertCount(2, $version->relations);

        foreach ($version->relations as $relation) {
            $this->assertEquals(Asset::class, $relation->versionable_type);
            // Make sure we have a version location
            $this->assertNotNull($relation->model_data['version_location'] ?? null);
        }
    }
}
