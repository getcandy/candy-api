<?php

namespace Tests;

/**
 * @group controllers
 * @group new
 * @group api
 */
class CategoryControllerTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->get($this->url('categories'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => [['id', 'attribute_data', 'depth']],
        ]);

        $this->assertEquals(200, $response->status());
    }
}
