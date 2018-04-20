<?php

namespace Tests;

use GetCandy\Api\Providers\ApiServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ApiServiceProvider::class
        ];
    }
}
