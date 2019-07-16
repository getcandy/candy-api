<?php

namespace Tests\Unit\Products\Factories;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Factories\ProductDuplicateFactory;

/**
 * @group current
 */
class ProductDuplicateFactoryTest extends TestCase
{
    public function test_can_duplicate_a_product()
    {
        $factory = $this->app->make(ProductDuplicateFactory::class);

        $productToCopy = Product::withoutGlobalScopes()->with([
            'variants',
            'routes',
            'assets',
            'customerGroups',
            'channels'
        ])->first();

        $skus = [];
        $routes = [];

        foreach ($productToCopy->variants as $variant) {
            $skus[] = [
                'current' => $variant->sku,
                'new' => str_random(),
            ];
        }

        foreach ($productToCopy->routes as $route) {
            $routes[] = [
                'current' => $route->slug,
                'new' => $route->slug . str_random(),
            ];
        }

        $newProduct = $factory->init($productToCopy)->duplicate(collect([
            'skus' => $skus,
            'routes' => $routes,
        ]));


        $this->assertSame($productToCopy->channels->count(), $newProduct->channels->count());
        $this->assertSame($productToCopy->customerGroups->count(), $newProduct->customerGroups->count());
        $this->assertSame($productToCopy->variants->count(), $newProduct->variants->count());
        $this->assertSame($productToCopy->assets->count(), $newProduct->assets->count());

        // Make sure it's visible and purchasable.
        foreach ($newProduct->customerGroups as $group) {
            $this->assertTrue((bool) $group->pivot->visible);
            $this->assertTrue((bool) $group->pivot->purchasable);
        }

        // Make sure it's not active on any channels
        foreach ($newProduct->channels as $channel) {
            $this->assertNull($group->pivot->published_at);
        }

        // Make sure each variant has a price
        foreach ($newProduct->variants as $variant) {
            $this->assertNotNull($variant->price);
            $this->assertNotNull($variant->stock);
        }

        // Make sure the original products variants/routes are intact
        $original = $productToCopy->load(['variants', 'routes']);

        foreach ($original->variants as $variant) {
            $sku = collect($skus)->first(function ($s) use ($variant) {
                return $s['current'] == $variant->sku;
            });
            $this->assertNotNull($sku);
        }

        foreach ($original->routes as $route) {
            $match = collect($routes)->first(function ($r) use ($route) {
                return $r['current'] == $route->slug;
            });
            $this->assertNotNull($match);
        }

        // Make sure that the new product has the correct variants/routes
        foreach ($newProduct->variants as $variant) {
            $sku = collect($skus)->first(function ($s) use ($variant) {
                return $s['new'] == $variant->sku;
            });
            $this->assertNotNull($sku);
        }

        foreach ($newProduct->routes as $route) {
            $match = collect($routes)->first(function ($r) use ($route) {
                return $r['new'] == $route->slug;
            });
            $this->assertNotNull($match);
        }
    }
}
