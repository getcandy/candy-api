<?php

namespace Tests\Unit\Shipping\Factories;

use GetCandy\Api\Core\GetCandy;
use GetCandy as GetCandyFacade;
use Route;
use Tests\TestCase;

/**
 * @group core
 */
class GetCandyTest extends TestCase
{
    public function test_can_set_routes()
    {
        GetCandy::routes();

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
        GetCandyFacade::routes();

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
        GetCandy::routes();

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
        GetCandy::routes([
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
        GetCandy::routes([
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
            GetCandy::routes();
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
