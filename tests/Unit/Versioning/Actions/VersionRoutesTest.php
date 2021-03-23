<?php

namespace Tests\Unit\Versioning\Actions;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use GetCandy\Api\Core\Versioning\Actions\VersionRoutes;
use Tests\TestCase;

/**
 * @group versioning
 */
class VersionRoutesTest extends TestCase
{
    public function test_can_version_model_routes()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        factory(Route::class, 2)->create([
            'element_type' => Product::class,
            'element_id' => $product->id,
        ]);

        $this->assertCount(2, $product->routes);

        $version = (new CreateVersion)->actingAs($user)->run([
            'model' => $product,
        ]);

        (new VersionRoutes)->actingAs($user)->run([
            'version' => $version,
            'model' => $product,
        ]);

        $this->assertCount(2, $version->relations);

        foreach ($version->relations as $relation) {
            $this->assertEquals(Route::class, $relation->versionable_type);
        }

        // Make sure our version has the correct channels
        foreach ($product->routes as $route) {
            $versionable = $version->relations->first(function ($version) use ($route) {
                return $version->versionable_id == $route->id && $version->versionable_type === get_class($route);
            });
            $this->assertNotNull($version);

            foreach ($route->getAttributes() as $attribute => $value) {
                $this->assertEquals($value, $versionable->model_data[$attribute]);
            }
        }
    }
}
