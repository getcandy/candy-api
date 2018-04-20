<?php

namespace Tests;

use GetCandy\Api\Products\Models\ProductFamily;

/**
 * @group controllers
 * @group api
 * @group product_families
 */
class ProductFamilyControllerTest extends TestCase
{
    protected $baseStructure = [
        'id',
        'attribute_data' => ['name'],
    ];

    public function testIndex()
    {
        $response = $this->get($this->url('product-families'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => [$this->baseStructure],
            'meta' => ['pagination'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testIndexWithAttributes()
    {
        $url = $this->url('product-families', [
            'includes' => 'attributes',
        ]);

        $response = $this->get($url, [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => [[
                'id',
                'attribute_data' => ['name'],
                'attributes' => [
                    'data',
                ],
            ]],
            'meta' => ['pagination'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testAttributesDontShowByDefault()
    {
        $url = $this->url('product-families');

        $response = $this->get($url, [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);
        $data = json_decode($response->getContent(), true);

        $this->assertTrue(empty($data['data'][0]['attribute_groups']));

        $this->assertEquals(200, $response->status());
    }

    public function testUnauthorisedIndex()
    {
        $response = $this->get($this->url('products'), [
            'Authorization' => 'Bearer foo.bar.bing',
            'Accept' => 'application/json',
        ]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testShow()
    {
        // Get a family
        $id = ProductFamily::first()->encodedId();

        $response = $this->get($this->url('product-families/'.$id), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => $this->baseStructure,
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testMissingShow()
    {
        $response = $this->get($this->url('product-families/123456'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $this->assertHasErrorFormat($response);

        $this->assertEquals(404, $response->status());
    }

    public function testStore()
    {
        $response = $this->post(
            $this->url('product-families'),
            [
                'name' =>  [
                    'en' => 'Foo',
                ],
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
            $this->url('product-families'),
            [],
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
        $id = ProductFamily::first()->encodedId();
        $response = $this->put(
            $this->url('product-families/'.$id),
            [
                'attributes' => [
                    'name' => [
                        'en' => 'Foo',
                    ],
                ],
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
            $this->url('product-families/123123'),
            [
                'attributes' => [
                    'name' => [
                        'en' => 'Foo',
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
        $product = ProductFamily::create([
            'attribute_data' => [
                'name' =>  [
                    'ecommerce' => [
                        'en' => 'Foo',
                    ],
                ],
            ],
        ]);

        $response = $this->delete(
            $this->url('product-families/'.$product->encodedId()),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );

        $this->assertEquals(204, $response->status());
    }
}
