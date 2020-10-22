<?php

namespace Tests\Unit\Shipping\Factories;

use GetCandy\Api\Core\GetCandy;
use GetCandy as GetCandyFacade;
use Illuminate\Support\Str;
use Route;
use Tests\TestCase;

class GetCandyTest extends TestCase
{
    /**
     * @group doo
     */
    public function test_can_resolve_services()
    {
        $services = [
            'assets',
            'attributes',
            'attributeGroups',
            'assetTransforms',
            'assetSources',
            'baskets',
            'basketLines',
            'categories',
            'collections',
            'currencies',
            'orders',
            'pages',
            'payment_types',
            'products',
            'productAssociations',
            'productVariants',
            'associationGroups',
            'roles',
            'savedBaskets',
            'settings',
            'shippingPrices',
            'shippingMethods',
            'shippingZones',
            'tags',
            'taxes',
            'layouts',
        ];

        foreach ($services as $service) {
            $instanceName = Str::snake($service);
            $this->assertInstanceOf(get_class(app("getcandy.{$instanceName}")), GetCandyFacade::{$service}());
        }
    }

    public function test_can_set_routes()
    {
        GetCandy::router();

        $testCases = array_merge($this->clientRoutes, $this->adminRoutes);

        $routes = collect(Route::getRoutes()->getIterator())->map(function ($route) {
            return $route->uri;
        });

        foreach ($testCases as $testCase) {
            $this->assertTrue($routes->contains($testCase));
        }
    }

    public function test_can_be_used_as_facade()
    {
        GetCandyFacade::router();

        $testCases = array_merge($this->clientRoutes, $this->adminRoutes);

        $routes = collect(Route::getRoutes()->getIterator())->map(function ($route) {
            return $route->uri;
        });

        foreach ($testCases as $testCase) {
            $this->assertTrue($routes->contains($testCase));
        }
    }

    public function test_default_middleware_gets_applied()
    {
        GetCandy::router();

        $defaultMiddleware = GetCandy::getDefaultMiddleware();

        $routeIterator = Route::getRoutes()->getIterator();

        $routes = collect($routeIterator)->map(function ($route) {
            return $route->uri;
        });

        $routes = array_merge($this->clientRoutes, $this->adminRoutes);

        foreach ($routeIterator as $routeObject) {
            if (in_array($routeObject->uri, $routes)) {
                foreach ($defaultMiddleware as $middleware) {
                    $this->assertContains($middleware, $routeObject->middleware());
                }
            }
        }
    }

    public function test_additional_middleware_can_be_set()
    {
        GetCandy::router([
            'middleware' => 'test.middleware',
        ]);

        $defaultMiddleware = GetCandy::getDefaultMiddleware();

        $routeIterator = Route::getRoutes()->getIterator();

        $routes = collect($routeIterator)->map(function ($route) {
            return $route->uri;
        });

        $routes = array_merge($this->clientRoutes, $this->adminRoutes);

        foreach ($routeIterator as $routeObject) {
            if (in_array($routeObject->uri, $routes)) {
                foreach ($defaultMiddleware as $middleware) {
                    $this->assertContains($middleware, $routeObject->middleware());
                    $this->assertContains('test.middleware', $routeObject->middleware());
                }
            }
        }
    }

    public function test_custom_route_prefix_can_be_applied()
    {
        GetCandy::router([
            'prefix' => 'foo',
        ]);

        $routeIterator = Route::getRoutes()->getIterator();

        collect($routeIterator)->map(function ($route) {
            return $route->uri;
        })->reject(function ($route) {
            // Remove any OAuth routes...
            return strpos($route, 'oauth') === 0;
        })->each(function ($route) {
            $this->assertTrue(strpos($route, 'foo') === 0);
        });
    }

    public function test_routes_can_be_used_within_a_group()
    {
        Route::group([
            'prefix' => 'foo',
        ], function () {
            GetCandy::router();
        });

        $routeIterator = Route::getRoutes()->getIterator();

        collect($routeIterator)->map(function ($route) {
            return $route->uri;
        })->reject(function ($route) {
            // Remove any OAuth routes...
            return strpos($route, 'oauth') === 0;
        })->each(function ($route) {
            $this->assertTrue(strpos($route, 'foo') === 0);
        });
    }
}
