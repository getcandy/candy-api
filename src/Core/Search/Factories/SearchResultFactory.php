<?php

namespace GetCandy\Api\Core\Search\Factories;

use CurrencyConverter;
use Elastica\ResultSet;
use League\Fractal\Manager;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\Collection;
use Elastica\Exception\InvalidException;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Search\Interfaces\SearchResultInterface;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTransformer;

class SearchResultFactory implements SearchResultInterface
{
    /**
     * The result set.
     *
     * @var ResultSet
     */
    protected $results;

    /**
     * The fractal instance.
     *
     * @var Manager
     */
    protected $fractal;

    /**
     * The search type.
     *
     * @var string
     */
    protected $type = 'product';

    /**
     * The ids of the searched models.
     *
     * @var array
     */
    protected $ids = [];

    /**
     * The result category.
     *
     * @var Category
     */
    protected $category = null;

    /**
     * The available transformers.
     *
     * @var array
     */
    private $transformers = [
        'product'  => ProductTransformer::class,
        'category' => CategoryTransformer::class,
    ];

    /**
     * The transformer to use.
     *
     * @var AbstractTranformer
     */
    protected $transformer;

    /**
     * The search meta.
     *
     * @var array
     */
    protected $meta = [];

    /**
     * The current page.
     *
     * @var int
     */
    protected $page = 1;

    /**
     * The model service.
     *
     * @var mixed
     */
    protected $service;

    /**
     * The current user.
     *
     * @var Model
     */
    protected $user = null;

    public function __construct(Manager $fractal)
    {
        $this->fractal = $fractal;
        $this->ids = collect($this->ids);
        $this->meta = collect($this->meta);
    }

    /**
     * Initialise the factory.
     *
     * @param ResultSet $results
     * @return void
     */
    public function init(ResultSet $results)
    {
        $this->results = $results;

        $ids = [];

        if ($results->count()) {
            foreach ($results as $r) {
                $this->ids->push($r->getSource()['id'] ?? null);
            }
        }

        if (! empty($this->transformers[$this->type])) {
            return $this->setTransformer(
                $this->transformers[$this->type]
            );
        }

        return $this;
    }

    /**
     * Set the model service.
     *
     * @param mixed $service
     * @return void
     */
    public function service($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Set the page.
     *
     * @param int $page
     * @return void
     */
    public function page($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Set the category.
     *
     * @param Category $category
     * @return void
     */
    public function category($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Set the current user.
     *
     * @param Model $user
     * @return void
     */
    public function user($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set the transformer to use.
     *
     * @param mixed $transformer
     * @return void
     */
    public function setTransformer($transformer = null)
    {
        if (is_object($transformer)) {
            $this->transformer = $transformer;

            return $this;
        }
        $this->transformer = new $transformer;

        return $this;
    }

    /**
     * Parse the fractal includes.
     *
     * @param string $includes
     * @return void
     */
    public function include($includes = [])
    {
        $this->fractal->parseIncludes($includes ?? []);

        return $this;
    }

    /**
     * Set the search type.
     *
     * @param string $type
     * @return void
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getMeta()
    {
        return [
            'sort' => $this->getSort(),
            'category_page' => (bool) $this->category,
            'pagination' => ['data' => $this->getPagination()],
            'aggregation' => ['data' => $this->getSearchAggregator()],
            'suggestions' => $this->getSuggestions(),
        ];
    }

    /**
     * Get the search result data.
     *
     * @return array
     */
    public function get()
    {
        $models = $this->service->getSearchedIds($this->ids, $this->user);
        $resource = new Collection($models, $this->transformer);
        $resource->setMeta(
            $this->getMeta()
        );

        return $this->fractal->createData($resource)->toArray();
    }

    /**
     * Maps the search sorting used to something we can use.
     *
     * @param ResultSet $results
     *
     * @return array
     */
    protected function getSort()
    {
        try {
            $params = $this->results->getQuery()->getParam('sort');
        } catch (InvalidException $e) {
            return;
        }

        return $params;
    }

    /**
     * Get the pagination for the results.
     *
     * @param array $results
     *
     * @return array
     */
    protected function getPagination()
    {
        $query = $this->results->getQuery();
        $data = $this->results->getResponse()->getData();

        if (isset($data['hits']['total']['value'])) {
            $hits = (int) $data['hits']['total']['value'] ?? 0;
        } else {
            $hits = (int) $data['hits']['total'] ?? 0;
        }

        $totalPages = ceil($hits / $query->getParam('size'));

        $pagination = [
            'total' => $hits,
            'count' => $this->results->count(),
            'per_page' => (int) $query->getParam('size'),
            'current_page' => (int) $this->page,
            'total_pages' => (int) ($totalPages <= 0 ? 1 : $totalPages),
        ];

        return $pagination;
    }

    /**
     * Get the search suggestions.
     *
     * @param ResultSet $results
     *
     * @return array
     */
    protected function getSuggestions()
    {
        $suggestions = [];

        foreach ($this->results->getSuggests() as $field => $item) {
            foreach ($item as $suggestion) {
                if (count($suggestion['options'])) {
                    foreach ($suggestion['options'] as $option) {
                        $suggestions[$field][] = $option;
                    }
                }
            }
        }

        return $suggestions;
    }

    public function getSuggestResults(ResultSet $results, $type = 'product')
    {
        $suggestions = $this->getSuggestions($results);

        if (empty($suggestions['suggest'])) {
            return [];
        }

        $ids = collect($suggestions['suggest'])->map(function ($item) {
            return $item['_id'];
        });

        return app('api')->{str_plural($type)}()->getSearchedIds($ids, true);
    }

    /**
     * Gets the aggregation fields for the results.
     *
     * @param array $results
     *
     * @return void
     */
    protected function getSearchAggregator()
    {
        if (! $this->results->hasAggregations()) {
            return [];
        }

        $aggs = $this->results->getAggregations();

        // Get our filterable attributes;
        $attributes = app('api')->attributes()->getFilterable();

        $results = [];

        $selected = [];
        $all = [];

        foreach ($aggs as $handle => $agg) {
            if ($handle == 'categories_post') {
                foreach ($agg['categories_post_filter']['categories_post_inner']['buckets'] as $bucket) {
                    $selected[] = $bucket['key'];
                }
            } elseif ($handle == 'categories_before') {
                foreach ($agg['categories_before_inner']['buckets'] as $bucket) {
                    $all[$bucket['key']] = $bucket['doc_count'];
                }
            } elseif ($handle == 'price') {
                // Get our currency.
                // $currency = app()->currencies
                // dd(CurrencyConverter::format($bucket));
                $buckets = collect($agg['buckets'])->map(function ($bucket) {
                    $label = 'between';

                    if ($bucket['from'] < 1) {
                        $label = 'less_then';
                    } elseif (empty($bucket['to'])) {
                        $label = 'over';
                    }

                    $label = trans('getcandy::search.price.aggregation.'.$label, [
                        'min' => app()->getInstance()->make(CurrencyConverterInterface::class)->format($bucket['from']),
                        'max' => app()->getInstance()->make(CurrencyConverterInterface::class)->format($bucket['to'] ?? 0),
                    ]);

                    $bucket['key'] = $label;

                    return $bucket;
                });

                $results[$handle] = ['buckets' => $buckets];
            } else {
                $results[$handle] = $agg;
                $results[$handle]['attribute'] = $attributes->first(function ($a) use ($handle) {
                    return $a->handle === $handle;
                });
            }
        }

        $selected = collect($selected);

        $models = app('api')->categories()->getSearchedIds(array_keys($all));

        foreach ($models as $category) {
            $category->aggregate_selected = $selected->contains($category->encodedId());
            $category->doc_count = $all[$category->encodedId()];
        }

        $resource = new Collection($models, new CategoryTransformer);

        $results['categories'] = app()->fractal->createData($resource)->toArray();

        return $results;
    }
}
