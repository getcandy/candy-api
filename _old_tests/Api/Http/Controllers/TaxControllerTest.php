<?php

namespace Tests;

use GetCandy\Api\Taxes\Models\Tax;

/**
 * @group api
 * @group controllers
 * @group tax
 */
class TaxControllerTest extends TestCase
{
    protected $baseStructure = [
        'id',
        'name',
        'percentage',
    ];

    public function testIndex()
    {
        $response = $this->get($this->url('taxes'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);
        $response->assertJsonStructure([
            'data',
            'meta' => ['pagination'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testUnauthorisedIndex()
    {
        $response = $this->get($this->url('taxes'), [
            'Authorization' => 'Bearer foo.bar.bing',
            'Accept' => 'application/json',
        ]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testShow()
    {
        // Get a channel
        $id = Tax::first()->encodedId();

        $response = $this->get($this->url('taxes/'.$id), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => $this->baseStructure,
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testMissingShow()
    {
        $response = $this->get($this->url('taxes/123456'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $this->assertHasErrorFormat($response);

        $this->assertEquals(404, $response->status());
    }

    public function testStore()
    {
        $response = $this->post(
            $this->url('taxes'),
            [
                'name' =>  'EU',
                'percentage' =>  '5',
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

    public function testStoreDuplicateValidationFail()
    {
        Tax::create([
            'name' => 'Foo',
            'default' => false,
            'percentage' => 5,
        ]);

        $response = $this->post(
            $this->url('taxes'),
            [
                'name' =>  'Foo',
                'percentage' =>  '5',
                'default' => false,
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

    public function testInvalidStore()
    {
        $response = $this->post(
            $this->url('taxes'),
            [],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'name', 'percentage',
        ]);

        $this->assertEquals(422, $response->status());
    }

    public function testUpdate()
    {
        $id = Tax::first()->encodedId();
        $response = $this->put(
            $this->url('taxes/'.$id),
            [
                'name' => 'VAT',
                'percentage' => 20,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );
        $this->assertEquals(200, $response->status());
    }

    public function testUniqueNameForRecordOnUpdate()
    {
        // Get an existing tax so we can use its name
        $existing = Tax::first();

        // Create a new tax to update
        $new = Tax::create([
            'name' => 'Foo',
            'default' => false,
            'percentage' => 50,
        ]);

        $response = $this->put(
            $this->url('taxes/'.$new->encodedId()),
            [
                'name' => $existing->name,
                'default' => false,
                'percentage' => 20,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $this->assertEquals(422, $response->status());

        $response->assertJsonStructure([
            'name',
        ]);
    }

    public function testMissingUpdate()
    {
        $response = $this->put(
            $this->url('taxes/123123'),
            [
                'name' => 'FOOBAR',
                'percentage' => 15,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $this->assertEquals(404, $response->status());
    }

    public function testDestroy()
    {
        $tax = Tax::create([
            'name' =>  'RIB',
            'percentage' =>  5000,
            'default' =>  false,
        ]);

        $response = $this->delete(
            $this->url('taxes/'.$tax->encodedId()),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );

        $this->assertEquals(204, $response->status());
    }
}
