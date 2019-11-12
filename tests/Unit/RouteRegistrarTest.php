<?php

namespace Tests\Unit\Shipping\Factories;

use GetCandy\Api\Core\RouteRegistrar;
use Route;
use Tests\TestCase;

/**
 * @group core
 */
class RouteRegistrarTest extends TestCase
{
    public function test_can_register_all_routes()
    {
        $registrar = app()->make(RouteRegistrar::class);

        $registrar->all();

        $testCases = array_merge($this->clientRoutes, $this->adminRoutes);

        $routes = collect(Route::getRoutes()->getIterator())->map(function ($route) {
            return $route->uri;
        });

        foreach ($testCases as $testCase) {
            $this->assertTrue($routes->contains($testCase));
        }
    }

    public function test_can_register_only_client_routes_with_correct_middleware()
    {
        $registrar = app()->make(RouteRegistrar::class);
        $registrar->forClients();

        $routeIterator = Route::getRoutes()->getIterator();

        $routes = collect($routeIterator)->map(function ($route) {
            return $route->uri;
        });

        foreach ($this->clientRoutes as $route) {
            $this->assertTrue($routes->contains($route));
        }

        foreach ($this->adminRoutes as $route) {
            $this->assertFalse($routes->contains($route));
        }

        foreach ($routeIterator as $routeObject) {
            if (in_array($routeObject->uri, $this->clientRoutes)) {
                $this->assertTrue(
                    in_array('api.client', $routeObject->middleware())
                );
            }
        }
    }

    public function test_can_register_only_admin_routes_with_correct_middleware()
    {
        $registrar = app()->make(RouteRegistrar::class);
        $registrar->forAdmins();

        $routeIterator = Route::getRoutes()->getIterator();

        $routes = collect($routeIterator)->map(function ($route) {
            return $route->uri;
        });

        foreach ($this->adminRoutes as $route) {
            $this->assertTrue($routes->contains($route));
        }

        foreach ($this->clientRoutes as $clientRoute) {
            $this->assertFalse($routes->contains($clientRoute));
        }

        foreach ($routeIterator as $routeObject) {
            if (in_array($routeObject->uri, $this->adminRoutes)) {
                $this->assertTrue(
                    in_array('auth:api', $routeObject->middleware())
                );
            }
        }
    }
}
