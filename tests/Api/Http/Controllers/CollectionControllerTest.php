<?php

namespace Tests;

use GetCandy\Api\Collections\Models\Collection;

/**
 * @group controllers
 * @group api
 */
class CollectionControllerTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->get($this->url('collections'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => [['id', 'attribute_data']],
            'meta' => ['pagination'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testUnauthorisedIndex()
    {
        $response = $this->get($this->url('collections'), [
            'Authorization' => 'Bearer foo.bar.bing',
            'Accept' => 'application/json',
        ]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testShow()
    {
        // Get a channel
        $id = Collection::first()->encodedId();

        $response = $this->get($this->url('collections/'.$id), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => ['id', 'attribute_data'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testMissingShow()
    {
        $response = $this->get($this->url('collections/123456'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $this->assertHasErrorFormat($response);

        $this->assertEquals(404, $response->status());
    }

    public function testStore()
    {
        $response = $this->post(
            $this->url('collections'),
            [
                'name' => [
                    'en' =>'Neon',
                ],
                'url' => 'neon',
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'data' => ['id', 'attribute_data'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testInvalidStore()
    {
        $response = $this->post(
            $this->url('collections'),
            [],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'name', 'url',
        ]);

        $this->assertEquals(422, $response->status());
    }

    public function testInvalidStoreAttributesFormating()
    {
        $response = $this->post(
            $this->url('collections'),
            [
                'name' => [
                    'ecommerce' => [
                        'ecommerce' => [
                            'en' => 'Foo',
                        ],
                    ],
                ],
                'url' => 'foo',
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'name',
        ]);

        $this->assertEquals(422, $response->status());
    }

    public function testUpdate()
    {
        $id = Collection::first()->encodedId();

        $response = $this->put(
            $this->url('collections/'.$id),
            [
                'attributes' => [
                    'name' => [
                        'ecommerce' => [
                            'en' => 'Neon',
                            'sv' => 'Noen',
                        ],
                    ],
                ],
                'neon' => 'neon',
                'default' => true,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'data' => ['id', 'attribute_data'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testMissingUpdate()
    {
        $response = $this->put(
            $this->url('collections/123123'),
            [
                'attributes' => [
                    'name' => [
                        'ecommerce' => [
                            'en' => 'Neon',
                            'sv' => 'Noen',
                        ],
                    ],
                ],
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );
        $this->assertEquals(404, $response->status());
    }

    public function testDestroy()
    {
        $collection = Collection::create([
            'attribute_data' => [
                'name' => [
                    'ecommerce' => [
                        'en' => 'Winter sales',
                    ],
                ],
            ],
        ]);

        $response = $this->delete(
            $this->url('collections/'.$collection->encodedId()),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );
        $this->assertEquals(204, $response->status());
    }
}
