<?php

namespace Tests\Unit\Payments\Providers;

use Event;
use Mockery;
use Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Handler\MockHandler;
use GetCandy\Api\Core\Payments\PaymentContract;
use GetCandy\Api\Core\Payments\PaymentResponse;
use GetCandy\Api\Core\Payments\Providers\SagePay;
use GetCandy\Api\Core\Payments\Models\Transaction;
use GetCandy\Api\Core\Orders\Factories\OrderFactory;
use GetCandy\Api\Core\Payments\ThreeDSecureResponse;

/**
 * @group payments
 */
class SagePayTest extends TestCase
{
    public function test_can_be_instantiated()
    {
        $manager = $this->app->make(PaymentContract::class);
        $this->assertInstanceOf(SagePay::class, $manager->driver('sagepay'));
    }

    public function test_can_get_client_token()
    {
        // Get our required response

        // Mock up our client
        $client = Mockery::mock('GuzzleHttp\Client');
        $client->shouldReceive('post')->once()->andReturn(
            $this->getMerchantTokenResponse()
        );
        $this->app->instance(Client::class, $client);

        $manager = $this->app->make(PaymentContract::class);
        $sagepay = $manager->driver('sagepay');

        $this->assertEquals('TEST_MERCHANT_KEY', $sagepay->getClientToken());
    }

    public function test_can_charge_a_card()
    {
        // Set up our order...
        $factory = $this->app->make(OrderFactory::class);
        // Event::fake();
        $basket = $this->getinitalbasket();
        $order = $factory->basket($basket)->resolve();

        // Mock up our client
        $mock = new MockHandler([
            $this->getMerchantTokenResponse(),
            $this->getSuccessfulChargeResponse($order->order_total),
        ]);

        $handler = HandlerStack::create($mock);

        $this->app->instance(Client::class, new Client([
            'handler' => $handler,
        ]));

        $manager = $this->app->make(PaymentContract::class);
        $sagepay = $manager->driver('sagepay');

        // dd($sagepay);
        $sagepay->order($order);
        // $sagepay->token('TESTTOKEN');

        $result = $sagepay->charge();
        $transaction = $result->getTransaction();

        $this->assertInstanceOf(PaymentResponse::class, $result);
        $this->assertInstanceOf(Transaction::class, $transaction);

        $this->assertTrue($transaction->success);
        $this->assertEquals('SagePay', $transaction->merchant);
        $this->assertEquals($order->order_total, $transaction->amount);
        $this->assertSame($order, $transaction->order);
        $this->assertEquals('TEST_TRANSACTION', $transaction->transaction_id);
        $this->assertEquals(1234, $transaction->last_four);
        $this->assertTrue($transaction->address_matched);
        $this->assertTrue($transaction->cvc_matched);
        $this->assertTrue($transaction->postcode_matched);
        $this->assertFalse($transaction->threed_secure);
    }

    /**
     * @group threed
     *
     * @return void
     */
    public function test_can_handle_a_threed_secure_response()
    {
        // Set up our order...
        $factory = $this->app->make(OrderFactory::class);
        // Event::fake();
        $basket = $this->getinitalbasket();
        $order = $factory->basket($basket)->resolve();

        // Mock up our client
        $mock = new MockHandler([
            $this->getMerchantTokenResponse(),
            $this->getSuccessfulChargeResponse($order->order_total, [
                'status' => '3DAuth',
                'statusCode' => '3DS',
                'paReq' => 1234,
                'acsUrl' => 5678,
            ]),
            $this->getThreedSecureResponse(),
            $this->getSuccessfulChargeResponse($order->order_total, [
                '3DSecure' => [
                    'status' => 'Authenticated',
                ],
            ]),
        ]);

        $handler = HandlerStack::create($mock);
        $this->app->instance(Client::class, new Client([
            'handler' => $handler,
        ]));

        $manager = $this->app->make(PaymentContract::class);
        $sagepay = $manager->driver('sagepay');

        $sagepay->order($order);

        $result = $sagepay->charge();

        $this->assertInstanceOf(ThreeDSecureResponse::class, $result);

        $this->assertEquals([
            'threedsecure' => true,
            'status' => '3DS',
            'transactionId' => 'TEST_TRANSACTION',
            'acsUrl' => 5678,
            'paRequest' => 1234,
        ], $result->params());

        $threed = $sagepay->processThreeD('TEST_TRANSACTION', 1234);

        $this->assertTrue($threed->success);
        $this->assertTrue($threed->threed_secure);
    }

    /**
     * @group refund
     */
    public function test_can_refund_a_transaction()
    {
        // Set up our order...
        $factory = $this->app->make(OrderFactory::class);
        // Event::fake();
        $basket = $this->getinitalbasket();
        $order = $factory->basket($basket)->resolve();

        // Mock up our client
        $mock = new MockHandler([
            $this->getRefundResponse(),
        ]);

        $handler = HandlerStack::create($mock);
        $this->app->instance(Client::class, new Client([
            'handler' => $handler,
        ]));

        $manager = $this->app->make(PaymentContract::class);
        $sagepay = $manager->driver('sagepay');

        $sagepay->order($order);

        $result = $sagepay->refund('TEST_TOKEN', $order->order_total, 'IMA REFUND!');

        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertEquals(-abs($order->order_total), $result->amount);
        $this->assertTrue($result->refund);
    }

    /**
     * Gets a mocked threed secure response.
     *
     * @return Response
     */
    protected function getThreedSecureResponse()
    {
        $body = Stream::factory(json_encode([
            'status' => 'Authenticated',
        ]));

        return new Response(200, [], $body);
    }

    /**
     * Get a refund response.
     *
     * @return Response
     */
    protected function getRefundResponse()
    {
        $body = Stream::factory(json_encode([
            'transactionId' => 'TEST_REFUND',
            'statusDetail' => 'refunded',
            'paymentMethod' => [
                'card' => [
                    'cardType' => 'Visa',
                    'lastFourDigits' => 1234,
                ],
            ],
        ]));

        return new Response(200, [], $body);
    }

    /**
     * Returns a mocked merchant token response.
     *
     * @return Response
     */
    protected function getMerchantTokenResponse()
    {
        $body = Stream::factory(json_encode([
            'expiry' => null,
            'merchantSessionKey' => 'TEST_MERCHANT_KEY',
        ]));

        return new Response(200, [], $body);
    }

    /**
     * Returns a mocked successful charge response.
     *
     * @param int $amount
     * @return Response
     */
    protected function getSuccessfulChargeResponse($amount = 10, $extra = [])
    {
        $content = json_encode(array_merge([
            'status' => 'Ok',
            'transactionId' => 'TEST_TRANSACTION',
            'avsCvcCheck' => [
                'address' => 'Matched',
                'securityCode' => 'Matched',
                'postalCode' => 'Matched',
            ],
            'paymentMethod' => [
                'card' => [
                    'cardType' => 'Mastercard',
                    'lastFourDigits' => '1234',
                ],
            ],
            '3DSecure' => [
                'status' => false,
            ],
            'amount' => [
                'totalAmount' => $amount,
            ],
        ], $extra));

        return new Response(200, [], Stream::factory($content));
    }
}
