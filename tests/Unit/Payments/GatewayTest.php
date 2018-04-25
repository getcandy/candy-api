<?php

namespace Tests\Unit\Payments;

/**
 * @group payments
 */
class GatewayTest extends PaymentsAbstract
{
    public function testHasCorrectGatewaySetUp()
    {
        $this->getProvider();
    }

    public function testHasCredentialsReady()
    {
        $config = $this->getGatewayConfig();

        $this->assertTrue(! empty(
            config('services.'.$config['gateway'])
        ));
        $this->assertTrue(! empty(
            config('services.'.$config['gateway'].'.merchants')
        ));
    }

    public function testCorrectMerchantForCurrency()
    {
        $config = $this->getGatewayConfig();
        $serviceConfig = config('services.'.$config['gateway']);
        $provider = $this->getProvider();
        $merchant = $provider->getMerchant();

        $this->assertEquals($serviceConfig['merchants']['default'], $merchant);

        $merchant = $provider->getMerchant('EUR');

        $this->assertEquals($serviceConfig['merchants']['eur'], $merchant);
    }

    public function testCanGetClientToken()
    {
        $this->assertTrue(
            ! empty($this->getProvider()->getClientToken())
        );
    }

    public function testCanValidateToken()
    {
        $provider = $this->getProvider();
        $this->assertTrue($provider->validateToken('fake-valid-nonce'));
        $this->assertFalse($provider->validateToken('some-other-token'));
    }
}
