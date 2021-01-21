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
        // https://github.com/wework/speccy/issues/360 once this is resolved there isn't the need for this cleanup
        $fullSpec = file_get_contents(__DIR__.'/../../openapi/openapi-full.yaml');
        $fullSpec = preg_replace("/\\\\(\n\s*)/", '', $fullSpec);
        file_put_contents(__DIR__.'/../../openapi/openapi-full.yaml', $fullSpec);

        // https://github.com/cebe/php-openapi/pull/67 once this is resolved we can use the non full again
        $this->buildOpenApiValidator(
            realpath(__DIR__.'/../../openapi/openapi-full.yaml')
        );
        GetCandy::router();
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
            'X-CANDY-HUB' => 1,
        ], $headers));
    }
}
