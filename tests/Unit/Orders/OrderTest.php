<?php

namespace Tests\Unit;

use Tests\TestCase;
use GetCandy\Api\Baskets\Models\Basket;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    public function testOrderIsCreatedFromBasket()
    {
        $this->assertTrue(false);
    }
}
