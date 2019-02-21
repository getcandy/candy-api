<?php

namespace Tests\Unit\ActivityLog\Factories;

use Tests\TestCase;
use Tests\Stubs\User;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\ActivityLog\Factories\ActivityLogFactory;
use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogFactoryInterface;

/**
 * @group logging
 */
class ActivityLogFactoryTest extends TestCase
{
    public function test_can_be_instantiated()
    {
        $service = $this->app->make(ActivityLogFactoryInterface::class);
        $this->assertInstanceOf(ActivityLogFactory::class, $service);
    }

    public function test_can_add_manual_log()
    {
        $factory = $this->app->make(ActivityLogFactoryInterface::class);

        $user = User::first();

        $order = Order::forceCreate([
            'currency' => 'GBP',
        ]);

        $factory->against($order)
            ->as($user)
            ->with([])
            ->action('test')
            ->log('system');

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'system',
            'causer_id' => $user->id,
            'description' => 'test',
            'subject_id' => $order->id,
            'subject_type' => get_class($order),
        ]);
    }
}
