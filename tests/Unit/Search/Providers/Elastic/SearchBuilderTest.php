<?php

namespace Tests\Unit\Search\Providers\Elastic;

use Mockery;
use Tests\TestCase;
use Elasticsearch\Client;
use GetCandy\Api\Core\Search\Providers\Elastic\SearchBuilder;

/**
 * @group search
 */
class SearchBuilderTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $builder = $this->app->make(SearchBuilder::class);
        $this->assertInstanceOf(SearchBuilder::class, $builder);
    }

    public function testCanSetTheSearchIndex()
    {
        // Mock our client.
        $client = Mockery::mock(Client::class)
            ->allows()
            ->getCurrentIndex('index_name')
            ->andReturns('foo');

        $this->app->instance(Client::class, $client);

        $builder = Mockery::mock(SearchBuilder::class);

        dd($builder->getClient());

        // $builder->allows()->getCurrentIndex()->andReturns('Foo');

        // $builder->setType('prdoduct');
        dd($builder);
    }
}
