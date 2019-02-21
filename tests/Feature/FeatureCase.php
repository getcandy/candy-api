<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Passport\Client;

abstract class FeatureCase extends TestCase
{
    protected $userToken;

    protected $clientToken;

    protected function getToken($user = false)
    {
        if ($user) {
            return $this->getUserToken();
        }

        return $this->getClientToken();
    }

    protected function getClientToken()
    {
        if ($this->clientToken) {
            return $this->clientToken;
        }

        $client = Client::first();

        dd($client);
    }
}
