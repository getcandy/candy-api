<?php

namespace Tests;

/**
 * @group middleware
 * @group api
 */
class SetLocaleMiddlewareTest extends TestCase
{
    public function testDefaultLanguage()
    {
        $response = $this->get($this->url('users'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
            'Accept' => 'application/json',
        ]);
        $response->assertJsonFragment(['lang' => 'en']);
    }

    public function testSetSpanishLanguage()
    {
        \GetCandy\Api\Languages\Models\Language::create([
            'lang' => 'es',
            'iso' => 'esp',
            'name' => 'Spanish',
            'enabled' => 1,
            'default' => 0,
        ]);
        $response = $this->get($this->url('users'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
            'Accept-Language' => 'es',
        ]);
        $response->assertJsonFragment(['lang' => 'es']);
    }
}
