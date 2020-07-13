<?php

namespace Tests\Unit\Routes;

use Tests\TestCase;
use GetCandy\Api\Core\Facades\Route;
use GetCandy\Api\Core\Routes\RouteFactory;

/**
 * @group routes
 */
class RouteFacadeTest extends TestCase
{
    public function test_can_be_resolved_from_ioc()
    {
        $instance = $this->app->make(Route::class);
        $this->assertInstanceOf(RouteFactory::class, $instance);
    }
}