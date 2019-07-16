<?php

namespace Tests\Unit\Orders\Services;

use Event;
use Tests\TestCase;
use GetCandy\Api\Core\Orders\Models\OrderLine;
use GetCandy\Api\Core\Orders\Factories\OrderFactory;
use GetCandy\Api\Core\Orders\Services\OrderLineService;

class OrderLineServiceTest extends TestCase
{
    public function test_can_add_orderline_without_tax()
    {
        $factory = $this->app->make(OrderFactory::class);
        Event::fake();

        $basket = $this->getinitalbasket();

        $order = $factory->basket($basket)->resolve();

        $service = $this->app->make(OrderLineService::class);

        $data = [
            'description' => 'Manual Line',
            'unit_price' => 500,
            'quantity' => 1,
            'tax_rate' => 0,
        ];

        $order = $service->store($order->encodedId(), $data, true);

        $manualLine = $order->lines->first(function ($l) {
            return $l->is_manual;
        });

        $this->assertInstanceOf(OrderLine::class, $manualLine);

        $this->assertEquals(0, $manualLine->tax_total);
    }
}
