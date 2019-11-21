<?php

namespace Tests\Feature;

use GetCandy;
use Laravel\Passport\Client;
use Tests\Stubs\User;
use Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;

abstract class FeatureCase extends TestCase
{
    protected $userToken;

    protected $clientToken;

    public function setUp() : void
    {
        parent::setUp();
        GetCandy::routes();
        $this->artisan('key:generate');
        $this->artisan('passport:install');
    }

    public function admin()
    {
        $user = User::first();
        $user->assignRole('admin');
        return $user;
    }

    public function actingAs(Authenticatable $user, $driver = null)
    {
        $token = $user->createToken('TestToken', [])->accessToken;

        // dd($token);
        $this->headers['Accept'] = 'application/json';
        $this->headers['Authorization'] = 'Bearer '.$token;

        return $this;
    }

    public function json($method, $uri, array $data = [], array $headers = [])
    {
        return parent::json($method, $uri, $data, array_merge($this->headers, $headers));
    }
}
