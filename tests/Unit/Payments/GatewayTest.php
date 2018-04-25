<?php

namespace Tests\Unit\Payments;

use TaxCalculator;
use Tests\TestCase;
use CurrencyConverter;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;
use GetCandy\Api\Core\Orders\Exceptions\OrderAlreadyProcessedException;

/**
 * @group payments
 */
class GatewayTest extends TestCase
{
    protected function getGatewayConfig()
    {
        $config = config('getcandy');

        $this->assertTrue(!empty($config['payments']['gateway']));

        $gateway = $config['payments']['gateway'];

        $this->assertTrue(!empty($config['payments']['providers'][$gateway]));

        $providerClassName = $config['payments']['providers'][$gateway];

        return [
            'gateway' => $gateway,
            'providers' => $config['payments']['providers'],
            'provider' => $providerClassName
        ];
    }

    protected function getProvider()
    {
        $config     = $this->getGatewayConfig();
        $provider   = app('api')->payments()->getProvider();

        $this->assertEquals($config['provider'], get_class($provider));

        return $provider;
    }

    public function testHasCorrectGatewaySetUp()
    {
        $this->getProvider();
    }

    public function testHasCredentialsReady()
    {
        $config = $this->getGatewayConfig();

        $this->assertTrue(!empty(
            config('services.' . $config['gateway'])
        ));
        $this->assertTrue(!empty(
            config('services.' . $config['gateway'] . '.merchants')
        ));
    }

    public function testCorrectMerchantForCurrency()
    {
        $config         =   $this->getGatewayConfig();
        $serviceConfig  =   config('services.' . $config['gateway']);
        $provider       =   $this->getProvider();
        $merchant       =   $provider->getMerchant();

        $this->assertEquals($serviceConfig['merchants']['default'], $merchant);

        $merchant = $provider->getMerchant('EUR');

        $this->assertEquals($serviceConfig['merchants']['eur'], $merchant);
    }

    public function testCanGetClientToken()
    {
        $this->assertTrue(
            !empty($this->getProvider()->getClientToken())
        );
    }

    public function testCanValidateToken()
    {
        $provider = $this->getProvider();
        $this->assertTrue($provider->validateToken('fake-valid-nonce'));
        $this->assertFalse($provider->validateToken('some-other-token'));
    }
}
