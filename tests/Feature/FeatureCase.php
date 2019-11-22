<?php

namespace Tests\Feature;

use DB;
use GetCandy;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\ClientRepository;
use Tests\Stubs\User;
use Tests\TestCase;

abstract class FeatureCase extends TestCase
{
    protected $userToken;

    protected $clientToken;

    protected $headers = [];

    public function setUp() : void
    {
        parent::setUp();
        GetCandy::routes();

        $clientRepository = new ClientRepository();
        $client = $clientRepository->createPersonalAccessClient(
            null,
            'Test Personal Access Client',
            '/'
        );

        DB::table('oauth_personal_access_clients')->insert([
            'client_id' => $client->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function getResponseContents($response)
    {
        return json_decode($response->content());
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

        $this->headers['Accept'] = 'application/json';
        $this->headers['Authorization'] = 'Bearer '.$token;

        return $this;
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.guards.api', [
            'driver' => 'passport',
            'provider' => 'users',
        ]);
    }

    public function json($method, $uri, array $data = [], array $headers = [])
    {
        return parent::json($method, $uri, $data, array_merge($this->headers, $headers));
    }
}
