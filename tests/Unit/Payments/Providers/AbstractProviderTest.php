<?php

namespace Tests\Unit\Payments\Providers;

use Event;
use Tests\TestCase;
use Tests\Stubs\TestPaymentProvider;
use GetCandy\Api\Core\Orders\Factories\OrderFactory;

/**
 * @group payments
 */
class AbstractProviderTest extends TestCase
{
    public function test_can_have_parameters_set()
    {
        $provider = new TestPaymentProvider;

        // Lets get an order...
        $factory = $this->app->make(OrderFactory::class);
        Event::fake();
        $basket = $this->getinitalbasket();
        $order = $factory->basket($basket)->resolve();

        $provider->order($order);
        $provider->token('TESTTOKEN');

        $this->assertSame($order, $provider->getOrder());
        $this->assertEquals('TESTTOKEN', $provider->getToken());
    }
}
