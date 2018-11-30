<?php

namespace Tests;

use GetCandy\Api\Currencies\Models\Currency;

/**
 * @group controllers
 * @group api
 */
class CurrencyControllerTest extends TestCase
{
    protected $baseStructure = [
        'id',
        'name',
        'code',
        'decimal',
        'thousand',
        'exchange_rate',
        'enabled',
        'default',
    ];

    public function testIndex()
    {
        $response = $this->get($this->url('currencies'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => [$this->baseStructure],
            'meta' => ['pagination'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testUnauthorisedIndex()
    {
        $response = $this->get($this->url('currencies'), [
            'Authorization' => 'Bearer foo.bar.bing',
            'Accept' => 'application/json',
        ]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testShow()
    {
        // Get a channel
        $id = Currency::first()->encodedId();

        $response = $this->get($this->url('currencies/'.$id), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => $this->baseStructure,
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testMissingShow()
    {
        $response = $this->get($this->url('currencies/123456'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $this->assertHasErrorFormat($response);

        $this->assertEquals(404, $response->status());
    }

    public function testStore()
    {
        $response = $this->post(
            $this->url('currencies'),
            [
                'name' =>  'Danish Krone',
                'code' =>  'DKK',
                'decimal' =>  ',',
                'thousand' =>  null,
                'exchange_rate' =>  '8',
                'format' => '45:price',
                'enabled' =>  true,
                'default' =>  false,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'data' => $this->baseStructure,
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testInvalidStore()
    {
        $response = $this->post(
            $this->url('currencies'),
            [],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'name', 'code', 'enabled', 'exchange_rate', 'format',
        ]);

        $this->assertEquals(422, $response->status());
    }

    public function testUpdate()
    {
        $id = Currency::first()->encodedId();
        $response = $this->put(
            $this->url('currencies/'.$id),
            [
                'name' => 'Neon',
                'default' => true,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $this->assertEquals(200, $response->status());
    }

    public function testMissingUpdate()
    {
        $response = $this->put(
            $this->url('currencies/123123'),
            [
                'name' => 'Neon',
                'default' => true,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $this->assertEquals(404, $response->status());
    }

    public function testDestroy()
    {
        $currency = Currency::create([
            'code' => 'EUR',
            'name' => 'Euro',
            'exchange_rate' => 1.15,
            'enabled' => true,
            'format' => '&#x20AC;{price}',
            'decimal_point' => '.',
            'thousand_point' => ',',
            'default' => false,
        ]);

        $response = $this->delete(
            $this->url('currencies/'.$currency->encodedId()),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );
        $this->assertEquals(204, $response->status());
    }

    public function testCannotDestroyLastChannel()
    {
        $id = Currency::first()->encodedId();
        $response = $this->delete(
            $this->url('currencies/'.$id),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );
        $this->assertEquals(422, $response->status());
    }
}
