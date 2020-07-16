<?php

namespace GetCandy\Api\Core\Search\Factories;

use CurrencyConverter;
use Elastica\Exception\InvalidException;
use Elastica\ResultSet;
use GetCandy;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use GetCandy\Api\Core\Search\Interfaces\SearchResultInterface;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class SearchResultFactory implements SearchResultInterface
{
    /**
     * The result set.
     *
     * @var \Elastica\ResultSet
     */
    protected $results;

    /**
     * The fractal instance.
     *
     * @var \League\Fractal\Manager
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
     * @var null|\GetCandy\Api\Core\Categories\Models\Category
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
     * @var mixed
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
     * @var null|\Illuminate\Foundation\Auth\User
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
     * @param  \Elastica\ResultSet  $results
     * @return $this
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
     * @param  mixed  $service
     * @return $this
     */
    public function service($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Set the page.
     *
     * @param  int  $page
     * @return $this
     */
    public function page($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Set the category.
     *
     * @param  \GetCandy\Api\Core\Categories\Models\Category  $category
     * @return $this
     */
    public function category($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @return $this
     */
    public function user($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set the transformer to use.
     *
     * @param  mixed  $transformer
     * @return $this
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
     * @param  array  $includes
     * @return $this
     */
    public function include($includes = [])
    {
        $this->fractal->parseIncludes($includes ?? []);

        return $this;
    }

    /**
     * Set the search type.
     *
     * @param  string  $type
     * @return $this
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

        return GetCandy::{str_plural($type)}()->getSearchedIds($ids, true);
    }

    /**
     * Gets the aggregation fields for the results.
     *
     * @return array
     */
    protected function getSearchAggregator()
    {
        if (! $this->results->hasAggregations()) {
            return [];
        }

        $aggs = $this->results->getAggregations();

        // Get our filterable attributes;
        $attributes = GetCandy::attributes()->getFilterable();

        $results = [];

        $selected = [];
        $all = [];

        foreach ($aggs as $handle => $agg) {
            if ($handle == 'categories_post') {
                foreach ($agg['categories_post_filter']['categories_post_inner']['buckets'] as $bucket) {
                    $selected[] = $bucket['key'];
                }
            } elseif ($handle == 'categories') {
                foreach ($agg['categories']['buckets'] as $bucket) {
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

        $models = GetCandy::categories()->getSearchedIds(array_keys($all));

        foreach ($models as $category) {
            $category->aggregate_selected = $selected->contains($category->encodedId());
            $category->doc_count = $all[$category->encodedId()];
        }

        $resource = new Collection($models, new CategoryTransformer);

        $results['categories'] = app()->fractal->createData($resource)->toArray();

        return $results;
    }
}
