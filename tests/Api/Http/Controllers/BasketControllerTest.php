<?php

namespace Tests;

use GetCandy\Api\Channels\Models\Channel;
use GetCandy\Api\Products\Models\ProductVariant;

/**
 * @group controllers
 * @group api
 * @group basket
 */
class BasketControllerTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->get($this->url('baskets'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data',
            'meta' => ['pagination'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testBasketCreationUpdateFunctionality()
    {
        // Get two variants...
        $variants = ProductVariant::take(2)->get();

        // dd();
        // // Create a new basket.
        $response = $this->post($this->url('baskets'), [
            'variants' => [
                [
                    'quantity' => 1,
                    'price' => 10.99,
                    'id' => $variants->first()->encodedId(),
                ],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => ['id'],
        ]);

        $data = json_decode($response->getContent(), true);

        // Store this for checks later
        $originalBasketId = $data['data']['id'];

        // Get our basket from the api...
        $response = $this->get($this->url('baskets/'.$originalBasketId.'?includes=lines'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $basket = json_decode($response->getContent(), true);

        $this->assertEquals(1, count($basket['data']['lines']['data']));

        $response = $this->post($this->url('baskets'), [
            'basket_id' => $originalBasketId,
            'variants' => [
                [
                    'quantity' => 1,
                    'price' => 10.99,
                    'id' => $variants->first()->encodedId(),
                ],
                [
                    'quantity' => 1,
                    'price' => 10.99,
                    'id' => $variants->nth(2, 1)->first()->encodedId(),
                ],
            ],
        ], [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $basket = json_decode($response->getContent(), true);

        $this->assertEquals($basket['data']['id'], $originalBasketId);

        $response = $this->get($this->url('baskets/'.$originalBasketId.'?includes=lines'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $basket = json_decode($response->getContent(), true);

        $this->assertEquals(2, count($basket['data']['lines']['data']));
    }

    // public function testUnauthorisedIndex()
    // {
    //     $response = $this->get($this->url('channels'), [
    //         'Authorization' => 'Bearer foo.bar.bing',
    //         'Accept' => 'application/json'
    //     ]);
    //     $this->assertEquals(401, $response->getStatusCode());
    // }

    // public function testShow()
    // {
    //     // Get a channel
    //     $id = Channel::first()->encodedId();

    //     $response = $this->get($this->url('channels/' . $id), [
    //         'Authorization' => 'Bearer ' . $this->accessToken()
    //     ]);

    //     $response->assertJsonStructure([
    //         'data' => ['id', 'name', 'default']
    //     ]);
    // $variants->nth (2, 1)->first ()
    //     $this->assertEquals(200, $response->status());
    // }

    // public function testMissingShow()
    // {
    //     $response = $this->get($this->url('channels/123456'), [
    //         'Authorization' => 'Bearer ' . $this->accessToken()
    //     ]);

    //     $this->assertHasErrorFormat($response);

    //     $this->assertEquals(404, $response->status());
    // }

    // public function testStore()
    // {
    //     $response = $this->post(
    //         $this->url('channels'),
    //         [
    //             'name' => 'Neon',
    //             'handle' => 'neon',
    //             'default' => true
    //         ],
    //         [
    //             'Authorization' => 'Bearer ' . $this->accessToken()
    //         ]
    //     );

    //     $response->assertJsonStructure([
    //         'data' => ['id', 'name', 'default']
    //     ]);

    //     $this->assertEquals(200, $response->status());
    // }

    // public function testInvalidStore()
    // {
    //     $response = $this->post(
    //         $this->url('channels'),
    //         [],
    //         [
    //             'Authorization' => 'Bearer ' . $this->accessToken()
    //         ]
    //     );

    //     $response->assertJsonStructure([
    //         'name', 'handle'
    //     ]);

    //     $this->assertEquals(422, $response->status());
    // }

    // public function testUpdate()
    // {
    //     $id = Channel::first()->encodedId();

    //     $response = $this->put(
    //         $this->url('channels/' . $id),
    //         [
    //             'name' => 'Neon',
    //             'neon' => 'neon',
    //             'default' => true
    //         ],
    //         [
    //             'Authorization' => 'Bearer ' . $this->accessToken()
    //         ]
    //     );

    //     $response->assertJsonStructure([
    //         'data' => ['id', 'name', 'default']
    //     ]);

    //     $this->assertEquals(200, $response->status());
    // }

    // public function testMissingUpdate()
    // {
    //     $response = $this->put(
    //         $this->url('channels/123123'),
    //         [
    //             'name' => 'Neon',
    //             'handle' => 'neon',
    //             'default' => true
    //         ],
    //         [
    //             'Authorization' => 'Bearer ' . $this->accessToken()
    //         ]
    //     );
    //     $this->assertEquals(404, $response->status());
    // }

    // public function testDestroy()
    // {
    //     $channel = Channel::create([
    //         'name' => 'Etsy',
    //         'handle' => 'etsy'
    //     ]);

    //     $response = $this->delete(
    //         $this->url('channels/' . $channel->encodedId()),
    //         [],
    //         ['Authorization' => 'Bearer ' . $this->accessToken()]
    //     );
    //     $this->assertEquals(204, $response->status());
    // }

    // public function testCannotDestroyLastChannel()
    // {
    //     $id = Channel::first()->encodedId();

    //     $response = $this->delete(
    //         $this->url('channels/' . $id),
    //         [],
    //         ['Authorization' => 'Bearer ' . $this->accessToken()]
    //     );

    //     $this->assertEquals(422, $response->status());
    // }
}
