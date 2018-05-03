<?php

namespace Tests;

use GetCandy\Api\Languages\Models\Language;

/**
 * @group controllers
 * @group api
 */
class LanguageControllerTest extends TestCase
{
    protected $baseStructure = [
        'id',
        'name',
        'iso',
        'lang',
    ];

    public function testIndex()
    {
        $response = $this->get($this->url('languages'), [
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
        $response = $this->get($this->url('languages'), [
            'Authorization' => 'Bearer foo.bar.bing',
            'Accept' => 'application/json',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testShow()
    {
        // Get a channel
        $id = Language::first()->encodedId();

        $response = $this->get($this->url('languages/'.$id), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => $this->baseStructure,
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testMissingShow()
    {
        $response = $this->get($this->url('languages/123456'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $this->assertHasErrorFormat($response);

        $this->assertEquals(404, $response->status());
    }

    public function testStore()
    {
        $response = $this->post(
            $this->url('languages'),
            [
                'name' =>  'Spanish',
                'lang' =>  'es',
                'iso' =>  'es',
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
            $this->url('languages'),
            [],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'name', 'iso', 'lang',
        ]);

        $this->assertEquals(422, $response->status());
    }

    public function testStoreUniqueCode()
    {
        $response = $this->post(
            $this->url('languages'),
            [
                'lang' => 'en',
                'iso' => 'gb',
                'name' => 'English',
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'iso',
        ]);

        $this->assertEquals(422, $response->status());
    }

    public function testUpdate()
    {
        $id = Language::first()->encodedId();
        $response = $this->put(
            $this->url('languages/'.$id),
            [
                'name' => 'Español',
                'lang' => 'es',
                'iso' => 'es',
                'default' => true,
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );
        $this->assertEquals(200, $response->status());
    }

    public function testUpdateUniqueCode()
    {
        Language::create([
            'name' => 'Foo',
            'iso' => 'foo',
            'lang' => 'foo',
        ]);

        $id = Language::first()->encodedId();
        $response = $this->put(
            $this->url('languages/'.$id),
            [
                'name' => 'Bar',
                'lang' => 'foo',
                'iso' => 'foo',
            ],
            [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]
        );

        $response->assertJsonStructure([
            'iso',
        ]);

        $this->assertEquals(422, $response->status());
    }

    public function testMissingUpdate()
    {
        $response = $this->put(
            $this->url('languages/123123'),
            [
                'name' => 'Español',
                'lang' => 'es',
                'iso' => 'es',
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
        $currency = Language::create([
            'name' =>  'Spanish',
            'lang' => 'es',
            'iso' => 'es',
            'default' =>  false,
        ]);

        $response = $this->delete(
            $this->url('languages/'.$currency->encodedId()),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );

        $this->assertEquals(204, $response->status());
    }

    public function testCannotDestroyLastLanguage()
    {
        $id = Language::first()->encodedId();
        $response = $this->delete(
            $this->url('languages/'.$id),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );

        $id = Language::first()->encodedId();
        $response = $this->delete(
            $this->url('languages/'.$id),
            [],
            ['Authorization' => 'Bearer '.$this->accessToken()]
        );
        $this->assertEquals(422, $response->status());
    }
}
