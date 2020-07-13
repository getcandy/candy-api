<?php

namespace Tests\Unit\Routes;

use DB;
use Tests\TestCase;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Routes\RouteFactory;
use GetCandy\Api\Core\Routes\RouteFactoryInterface;

/**
 * @group routes
 */
class RouteFactoryTest extends TestCase
{
    public function test_can_be_resolved_from_ioc()
    {
        $factory = $this->app->make(RouteFactoryInterface::class);
        $this->assertInstanceOf(RouteFactory::class, $factory);
    }

    public function test_can_encode_and_decode_ids_from_config()
    {
        $factory = $this->app->make(RouteFactoryInterface::class);
        $config = config('hashids.connections.route');

        $encodedId = $factory->encodeId(1);

        $this->assertIsString($encodedId);
        $this->assertEquals($config['length'], strlen($encodedId));

        $decodedId = $factory->decodeId($encodedId);

        $this->assertIsNumeric($decodedId);
        $this->assertEquals(1, $decodedId);
    }

    public function test_can_get_route_by_slug_from_database()
    {
        $newRouteId = DB::table('routes')->insertGetId([
            'element_id' => 1,
            'element_type' => 'Foobar',
            'slug' => 'foobar'
        ]);

        $factory = $this->app->make(RouteFactoryInterface::class);

        $route = $factory->get('foobar', 'Foobar');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($newRouteId, $route->id);
    }

    public function test_can_get_route_by_slug_and_path()
    {
        DB::table('routes')->insertGetId([
            'element_id' => 1,
            'element_type' => 'Foobar',
            'slug' => 'bar',
        ]);

        $newRouteId = DB::table('routes')->insertGetId([
            'element_id' => 1,
            'element_type' => 'Foobar',
            'slug' => 'bar',
            'path' => 'foo'
        ]);

        $factory = $this->app->make(RouteFactoryInterface::class);

        $route = $factory->get('bar', 'Foobar', 'foo');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($newRouteId, $route->id);
    }

    public function test_can_get_route_by_slug_and_path_and_element()
    {
        DB::table('routes')->insertGetId([
            'element_id' => 1,
            'element_type' => 'Foo',
            'slug' => 'foo',
        ]);

        $expectedRouteId = DB::table('routes')->insertGetId([
            'element_id' => 1,
            'element_type' => 'Bar',
            'slug' => 'foo',
        ]);

        $factory = $this->app->make(RouteFactoryInterface::class);

        $route = $factory->get('foo', 'Bar');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals($expectedRouteId, $route->id);
    }
}
