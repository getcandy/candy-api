<?php

namespace Tests\Unit\Payments;

use GetCandy\Api\Core\Orders\Models\Order;

/**
 * @group current
 */
class ProviderTest extends PaymentsAbstract
{
    public function testCanPayOffline()
    {
        $config = $this->getGatewayConfig();

        $provider = app('api')->payments()->setProvider('offline')->getProvider();

        $this->assertEquals(
            $config['providers']['offline'],
            get_class($provider)
        );

        $this->assertTrue($provider->charge(null, new Order));
    }
}
