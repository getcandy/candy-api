<?php

namespace Tests;

use Event;
use GetCandy\Api\Layouts\Models\Layout;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Attributes\Models\Attribute;
use GetCandy\Api\Collections\Models\Collection;
use GetCandy\Api\Products\Models\ProductFamily;

/**
 * @group controllers
 * @group api
 * @group products
 */
class ProductControllerTest extends TestCase
{
    protected $baseStructure = [
        'id',
        'attribute_data' => ['name'],
    ];

    public function testIndex()
    {
        $response = $this->get($this->url('products'), [
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
        $url = $this->url('products', [
            'includes' => 'attribute_groups,attribute_groups.attributes',
        ]);

        $response = $this->get($url, [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => [[
                'id',
                'attribute_data' => ['name'],
                'attribute_groups' => [
                    'data' => [
                        [
                            'id', 'attributes' => [
                                'data' => [
                                    ['id'],
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
            'meta' => ['pagination'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testAttributesDontShowByDefault()
    {
        $url = $this->url('products');

        $response = $this->get($url, [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);
        $data = json_decode($response->getContent(), true);

        $this->assertTrue(empty($data['data'][0]['attribute_groups']));

        $this->assertEquals(200, $response->status());
    }

    public function testUpdateAttributes()
    {
        $product = Product::first();

        $attributes = Attribute::limit(2)->offset(2)->get();

        $ids = [];

        foreach ($attributes as $attribute) {
            $ids[] = $attribute->encodedId();
        }

        $response = $this->post(
            $this->url('products/'.$product->encodedId().'/attributes'),
            [
                'attributes' =>  $ids,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->status());
    }

    public function testUpdateCollections()
    {
        $product = Product::first();

        $collections = Collection::take(2)->get();

        $ids = [];

        foreach ($collections as $collection) {
            $ids[] = $collection->encodedId();
        }

        $response = $this->post(
            $this->url('products/'.$product->encodedId().'/collections'),
            [
                'collections' =>  $ids,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->status());
    }

    public function testIndexWithFamily()
    {
        $url = $this->url('products', [
            'includes' => 'family',
        ]);

        $response = $this->get($url, [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => [[
                'id',
                'attribute_data' => ['name'],
                'family' => ['data' => ['id']],
            ]],
            'meta' => ['pagination'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testIndexWithFamilyAndAttributes()
    {
        $url = $this->url('products', [
            'includes' => 'family,attribute_groups,attribute_groups.attributes',
        ]);

        $response = $this->get($url, [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => [[
                'id',
                'attribute_data' => ['name'],
                'attribute_groups' => [
                    'data' => [
                        [
                            'id', 'attributes' => [
                                'data' => [
                                    ['id'],
                                ],
                            ],
                        ],
                    ],
                ],
                'family' => ['data' => ['id']],
            ]],
            'meta' => ['pagination'],
        ]);

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
        // Get a channel
        $id = Product::first()->encodedId();

        $response = $this->get($this->url('products/'.$id), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => $this->baseStructure,
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testMissingShow()
    {
        $response = $this->get($this->url('products/123456'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $this->assertHasErrorFormat($response);

        $this->assertEquals(404, $response->status());
    }

    public function testStore()
    {
        Event::fake();

        $family = ProductFamily::first();

        $layout = Layout::first()->encodedId();

        $response = $this->post(
            $this->url('products'),
            [
                'name' => [
                    'en' => 'Spring water',
                ],
                'url' => 'spring-water',
                'sku' => 'Foo',
                'stock' => 1,
                'price' => 29.99,
                'family_id' => $family->encodedId(),
                'layout_id' => $layout,
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
            $this->url('products'),
            [],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'name', 'family_id',
        ]);

        $this->assertEquals(422, $response->status());
    }

    public function testInvalidStoreAttributesFormating()
    {
        $family = ProductFamily::first();

        $layout = Layout::first()->encodedId();

        $response = $this->post(
            $this->url('products'),
            [
                'name' => [
                    'ecommerce' => [
                        'ecommerce' => [
                            'en' => 'Foo',
                        ],
                    ],
                ],
                'url' => 'Foo',
                'stock' => 1,
                'price' => 19.99,
                'sku' => 'Foo',
                'family_id' => $family->encodedId(),
                'layout_id' => $layout,
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

    // public function testInvalidLanguageStore()
    // {
    //     $family = ProductFamily::create([
    //         'attribute_data' => ['name' => ['ecommerce' => ['en' => 'Foo bar']]]
    //     ]);

    //     $layout = Layout::first()->encodedId();

    //     $response = $this->post(
    //         $this->url('products'),
    //         [
    //             'attributes' => [
    //                 'name' =>  [
    //                     'ecommerce' => [
    //                         'en' => 'Foo'
    //                     ]
    //                 ]
    //             ],
    //             'sku' => 'Foo',
    //             'family_id' => $family->encodedId(),
    //             'slug' => 'spring-water',
    //             'layout_id' => $layout,
    //         ],
    //         [
    //             'Authorization' => 'Bearer ' . $this->accessToken()
    //         ]
    //     );

    //     $this->assertHasErrorFormat($response);
    //     $this->assertEquals(422, $response->status());
    // }

    public function testUpdate()
    {
        Event::fake();

        $productId = Product::first()->encodedId();

        $attributes = app('api')->products()->getAttributes($productId);
        $defaultChannel = app('api')->channels()->getDefaultRecord();
        $defaultLanguage = app('api')->languages()->getDefaultRecord();

        $data = [];

        foreach ($attributes as $attribute) {
            if ($attribute->required) {
                $data[$attribute->handle][$defaultChannel->handle][$defaultLanguage->lang] = 'Foo';
            }
        }

        $response = $this->put(
            $this->url('products/'.$productId),
            [
                'attributes' => $data,
                'default' => true,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $this->assertEquals(200, $response->status());
    }

    public function testAttributeUpdate()
    {
        Event::fake();
        $product = Product::first();

        $attribute = new \GetCandy\Api\Attributes\Models\Attribute();
        $attribute->name = ['en' => 'Foo bar', 'sv' => 'Fee ber'];
        $attribute->handle = 'foo-bar';
        $attribute->position = 1;
        $attribute->group_id = \GetCandy\Api\Attributes\Models\AttributeGroup::first()->id;
        $attribute->required = true;
        $attribute->save();

        $response = $this->post(
            $this->url('products/'.$product->encodedId().'/attributes'),
            [
                'attributes' => [
                    $attribute->encodedId(),
                ],
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );
        $this->assertTrue($product->attributes->count() == 1);
    }

    public function testMissingUpdate()
    {
        $response = $this->put(
            $this->url('products/123123'),
            [
                'attributes' => [
                    'name' =>  [
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
        $product = Product::create([
            'attribute_data' => [
                'name' =>  [
                    'en' => 'Foo',
                ],
            ],
        ]);

        $response = $this->delete(
            $this->url('products/'.$product->encodedId()),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );

        $this->assertEquals(204, $response->status());
    }
}
