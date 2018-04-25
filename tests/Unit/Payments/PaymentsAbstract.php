<?php

namespace Tests\Unit\Payments;

use Tests\TestCase;

/**
 * @group payments
 */
abstract class PaymentsAbstract extends TestCase
{
    protected function getGatewayConfig()
    {
        $config = config('getcandy');

        $this->assertTrue(! empty($config['payments']['gateway']));

        $gateway = $config['payments']['gateway'];

        $this->assertTrue(! empty($config['payments']['providers'][$gateway]));

        $providerClassName = $config['payments']['providers'][$gateway];

        return [
            'gateway' => $gateway,
            'providers' => $config['payments']['providers'],
            'provider' => $providerClassName,
        ];
    }

    protected function getProvider()
    {
        $config = $this->getGatewayConfig();
        $provider = app('api')->payments()->getProvider();

        $this->assertEquals($config['provider'], get_class($provider));

        return $provider;
    }
}
