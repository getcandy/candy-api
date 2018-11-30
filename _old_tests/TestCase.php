<?php

namespace Tests;

use Artisan;
use Laravel\Passport\Client;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp()
    {
        // Delete stub database for force a refresh of data
        if (file_exists(__DIR__.'/../storage/testing/stub.sqlite')) {
            unlink(__DIR__.'/../storage/testing/stub.sqlite');
        }

        // Delete testing database if exists
        if (file_exists(__DIR__.'/../storage/testing/database.sqlite')) {
            unlink(__DIR__.'/../storage/testing/database.sqlite');
        }

        touch(__DIR__.'/../storage/testing/database.sqlite');

        ini_set('memory_limit', '1G');

        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'TestingSeeder']);
    }

    protected function url($path, $query = null)
    {
        $url = '/api/'.config('getcandy_api.version', 'v1').'/'.$path.($query ? '?'.http_build_query($query) : null);

        return $url;
    }

    protected function getContent($response)
    {
        return json_decode($response->getContent(), true);
    }

    protected function accessToken()
    {
        $client = Client::first();

        $response = $this->post('/oauth/token', [
            'username' => 'alec@neondigital.co.uk',
            'password' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'grant_type' => 'password',
        ], ['Accept' => 'application/json']);

        $content = $this->getContent($response);

        $response->assertJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        return $content['access_token'];
    }

    protected function assertHasErrorFormat($response)
    {
        $response->assertJsonStructure([
            'error' => ['http_code', 'message'],
        ]);
    }
}
