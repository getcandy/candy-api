<?php

namespace Tests\Unit\Orders\Services;

use Tests\TestCase;
use Tests\Stubs\TestPaymentManager;
use GetCandy\Api\Core\Payments\PaymentContract;
use GetCandy\Api\Core\Payments\Models\PaymentType;
use GetCandy\Api\Core\Orders\Factories\OrderFactory;
use GetCandy\Api\Core\Orders\Factories\OrderProcessingFactory;
use GetCandy\Api\Core\Orders\Interfaces\OrderProcessingFactoryInterface;

class OrderProcessingFactoryTest extends TestCase
{
    public function test_can_be_instantiated()
    {
        $service = $this->app->make(OrderProcessingFactoryInterface::class);
        $this->assertInstanceOf(OrderProcessingFactory::class, $service);
    }

    public function test_can_set_order_on_factory()
    {
        $orders = $this->app->make(OrderFactory::class);
        $factory = $this->app->make(OrderProcessingFactory::class);
        $basket = $this->getinitalbasket();
        $order = $orders->basket($basket)->resolve();

        $factoryOrder = $factory->order($order)->getOrder();

        $this->assertSame($order, $factoryOrder);
    }

    public function test_can_be_processed()
    {
        // First swap out the PaymentContract instance for our stub.
        $this->app->instance(PaymentContract::class, new TestPaymentManager);

        $orders = $this->app->make(OrderFactory::class);
        $factory = $this->app->make(OrderProcessingFactory::class);
        $basket = $this->getinitalbasket();
        $order = $orders->basket($basket)->resolve();

        // Create a payment type.
        $paymentType = PaymentType::forceCreate([
            'name' => 'Credit/Debit Card',
            'driver' => 'sagepay',
            'success_status' => 'Complete',
        ]);

        // use nonce 1234 for an invalid response
        $order = $factory
            ->order($order)
            ->nonce(123456)
            ->notes('notes')
            ->customerReference(1234)
            ->provider($paymentType)
            ->resolve();

        $this->assertEquals('notes', $order->notes);
        $this->assertEquals(1234, $order->customer_reference);
        $this->assertNotNull($order->placed_at);
        $this->assertCount(1, $order->transactions);

        $transaction = $order->transactions->first();

        $this->assertEquals($order->sub_total, $transaction->amount);
    }
}
