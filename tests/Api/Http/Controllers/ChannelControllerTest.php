<?php

namespace Tests;

use GetCandy\Api\Channels\Models\Channel;

/**
 * @group controllers
 * @group api
 */
class ChannelControllerTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->get($this->url('channels'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => [['id', 'name', 'default']],
            'meta' => ['pagination'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testUnauthorisedIndex()
    {
        $response = $this->get($this->url('channels'), [
            'Authorization' => 'Bearer foo.bar.bing',
            'Accept' => 'application/json',
        ]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testShow()
    {
        // Get a channel
        $id = Channel::first()->encodedId();

        $response = $this->get($this->url('channels/'.$id), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => ['id', 'name', 'default'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testMissingShow()
    {
        $response = $this->get($this->url('channels/123456'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $this->assertHasErrorFormat($response);

        $this->assertEquals(404, $response->status());
    }

    public function testStore()
    {
        $response = $this->post(
            $this->url('channels'),
            [
                'name' => 'Neon',
                'handle' => 'neon',
                'default' => true,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'data' => ['id', 'name', 'default'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testInvalidStore()
    {
        $response = $this->post(
            $this->url('channels'),
            [],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'name', 'handle',
        ]);

        $this->assertEquals(422, $response->status());
    }

    public function testUpdate()
    {
        $id = Channel::first()->encodedId();

        $response = $this->put(
            $this->url('channels/'.$id),
            [
                'name' => 'Neon',
                'neon' => 'neon',
                'default' => true,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'data' => ['id', 'name', 'default'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testMissingUpdate()
    {
        $response = $this->put(
            $this->url('channels/123123'),
            [
                'name' => 'Neon',
                'handle' => 'neon',
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
        $channel = Channel::create([
            'name' => 'Etsy',
            'handle' => 'etsy',
        ]);

        $response = $this->delete(
            $this->url('channels/'.$channel->encodedId()),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );
        $this->assertEquals(204, $response->status());
    }

    public function testCannotDestroyLastChannel()
    {
        $id = Channel::first()->encodedId();

        $response = $this->delete(
            $this->url('channels/'.$id),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );

        $this->assertEquals(422, $response->status());
    }
}
