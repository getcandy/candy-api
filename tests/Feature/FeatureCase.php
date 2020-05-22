<?php

namespace Tests\Feature;

use GetCandy;
use Illuminate\Contracts\Auth\Authenticatable;
use NeonDigital\OpenApiValidator\ValidatesWithOpenApi;
use Tests\Stubs\User;
use Tests\TestCase;

abstract class FeatureCase extends TestCase
{
    use ValidatesWithOpenApi;

    protected $userToken;

    protected $clientToken;

    protected $headers = [];

    protected $validator;

    public function setUp(): void
    {
        parent::setUp();
        GetCandy::routes();
        $this->artisan('passport:install');

        $this->buildOpenApiValidator(
            realpath(__DIR__.'/../../open-api.yaml')
        );
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
        $this->headers['X-CANDY-HUB'] = 1;

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
        // dd($data);
        return parent::json($method, $uri, $data, array_merge($this->headers, $headers));
    }
}
