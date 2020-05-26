<?php

namespace Tests\Feature;

use GetCandy;
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
        $this->buildOpenApiValidator(
            realpath(__DIR__.'/../../open-api.yaml')
        );
        GetCandy::routes();
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

    public function json($method, $uri, array $data = [], array $headers = [])
    {
        return parent::json($method, $uri, $data, array_merge([
            'X-CANDY-HUB' => 1
        ], $headers));
    }
}
