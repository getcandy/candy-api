<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch;

use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Drivers\AbstractSearchDriver;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\FetchClient;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\IndexCategories;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\IndexProducts;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching\Search;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\SetIndexLive;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Events\IndexingCompleteEvent;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class Elasticsearch extends AbstractSearchDriver
{
    public function __construct(Dispatcher $events, Container $container)
    {
        $events->listen(IndexingCompleteEvent::class, function ($event) {
            SetIndexLive::run([
                'indexes' => $event->indexes,
                'type' => $event->type,
            ]);
        });
    }

    public function index($documents, $final = false)
    {
        $type = get_class($documents->first());
        switch ($type) {
            case Product::class:
                IndexProducts::run([
                    'products' => $documents,
                    'uuid' => $this->reference,
                    'final' => $final,
                ]);
                break;
            case Category::class:
                IndexCategories::run([
                    'categories' => $documents,
                    'uuid' => $this->reference,
                    'final' => $final,
                ]);
                break;
            default:
            break;
        }
    }

    public function update($documents)
    {
        if (! $documents instanceof Collection) {
            $documents = collect([$documents]);
        }

        $client = FetchClient::run();

        $prefix = config('getcandy.search.index_prefix');

        $type = get_class($documents->first()) == Product::class ? 'products' : 'categories';

        $existing = collect($client->getStatus()->getIndexNames())->filter(function ($indexName) use ($prefix, $type) {
            return strpos($indexName, "{$prefix}_{$type}") !== false;
        });

        $reference = substr($existing->first(), strrpos($existing->first(), '_') + 1);

        $this->onReference($reference)->index($documents, false);
    }

    public function search($data)
    {
        if ($data instanceof Request) {
            return (new Search)->runAsController($data);
        }

        return Search::run([
            'search_type' => $data['type'] ?? 'products',
            'filters' => $data['filters'] ?? [],
            'aggregates' => $data['aggregates'] ?? [],
            'term' => $data['term'] ?? null,
            'language' => $data['language'] ?? app()->getLocale(),
        ]);
    }

    public function config()
    {
        return [
            'features' => [
                'faceting',
                'aggregates',
            ],
        ];
    }
}
