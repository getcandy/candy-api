<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Query;
use Elastica\Query\Wildcard;
use Elastica\Suggest;
use GetCandy\Api\Core\Search\ClientContract;
use GetCandy\Api\Core\Search\Providers\Elastic\Sorts\CategorySort;

class Search implements ClientContract
{
    /**
     * The Search Builder.
     *
     * @var \GetCandy\Api\Core\Search\Providers\Elastic\SearchBuilder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $aggregators = [
        'priceRange',
    ];

    /**
     * @var \GetCandy\Api\Core\Search\Providers\Elastic\FilterSet
     */
    protected $filterSet;

    /**
     * @var array
     */
    protected $categories = [];

    /**
     * @var array
     */
    protected $sorts = [];

    /**
     * The filters to apply to the search.
     *
     * @var null|array|\Illuminate\Support\Collection
     */
    protected $filters = null;

    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Set the user on the search.
     *
     * @param  null|\Illuminate\Foundation\Auth\User  $user
     * @return $this
     */
    public function user($user = null)
    {
        $this->builder->setUser($user);

        return $this;
    }

    /**
     * Set the keywords on the builder.
     *
     * @param  string  $keywords
     * @return $this
     */
    public function keywords($keywords = null)
    {
        if ($keywords) {
            $this->builder->setTerm($keywords);
        }

        return $this;
    }

    /**
     * Set the sorting on the builder.
     *
     * @param  array  $sorts
     * @return $this
     */
    public function sorting($sorts)
    {
        $this->sorts = $sorts;
        $this->builder->setSorting($sorts);

        return $this;
    }

    /**
     * Set up the pagination.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @return $this
     */
    public function pagination($page = 1, $perPage = 30)
    {
        $this->builder->setLimit($perPage)
            ->setOffset(($page - 1) * $perPage);

        return $this;
    }

    /**
     * Set the channel to filter on.
     *
     * @param  string|null  $channel
     * @return $this
     */
    public function on($channel = null)
    {
        $this->builder->setChannel($channel);

        return $this;
    }

    /**
     * Set the index.
     *
     * @param  string  $type
     * @return $this
     */
    public function against($type)
    {
        $this->builder->setType($type);

        return $this;
    }

    /**
     * Set the categories.
     *
     * @param  array  $categories
     * @return $this
     */
    public function categories($categories = [])
    {
        if (count($categories)) {
            $this->categories = $categories;
            $filter = $this->findFilter('Category');
            $filter->process($categories);
            $this->builder->addFilter($filter);
        }

        return $this;
    }

    /**
     * Set the filters on the search.
     *
     * @param  array  $filters
     * @return $this
     */
    public function filters($filters = [])
    {
        foreach ($filters as $filter => $value) {
            $object = $this->findFilter($filter);
            if ($object && $object = $object->process($value, $filter)) {
                $this->builder->addFilter($object);
            }
        }

        return $this;
    }

    /**
     * Set the search language.
     *
     * @param  string  $lang
     * @return $this
     */
    public function language($lang = 'en')
    {
        $this->builder->setLang($lang);

        return $this;
    }

    /**
     * Set the suggestions.
     *
     * @param  string  $keywords
     * @return \Elastica\ResultSet
     */
    public function suggest($keywords)
    {
        $search = $this->builder->getSearch();

        $suggest = new \Elastica\Suggest;
        $term = new \Elastica\Suggest\Completion('suggest', 'name.suggest');
        $term->setText($keywords);
        $suggest->addSuggestion($term);

        return $search->search($suggest);
    }

    /**
     * Perform a wildcard search on an SKU.
     *
     * @param  string  $sku
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function searchSkus($sku, $limit = 10)
    {
        $sku = strtolower($sku);
        $search = $this->builder->getSearch();

        $query = new Query;
        $wildcard = new Wildcard('sku.lowercase', "*{$sku}*");
        $query->setQuery($wildcard);
        $query->setParam('size', $limit);
        $query->setHighlight([
            'pre_tags' => ['', ''],
            'post_tags' => ['', ''],
            'fields' => [
                'sku.lowercase' => [
                    'type' => 'unified',
                ],
            ],
        ]);

        $results = collect($search->search($query)->getResults());

        $products = collect();

        $results->each(function ($result) use ($products) {
            $skus = collect($result->getHighlights()['sku.lowercase']);
            $skus->each(function ($sku) use ($products, $result) {
                $products->push([
                    'name' => $result->name,
                    'breadcrumbs' => $result->breadcrumbs,
                    'sku' => $sku,
                ]);
            });
        });

        return $products;
    }

    /**
     * Searches the index.
     *
     * @param  bool  $rank
     * @return \Elastica\ResultSet
     */
    public function search($rank = true)
    {
        $builder = $this->builder;

        $builder
            ->withAggregations()
            ->useCustomerFilters();

        if ($channel = $builder->getChannel()) {
            $channelFilter = $this->findFilter('Channel');
            $channelFilter->process($channel);
            $builder->addFilter($channelFilter);
        }

        if (count($this->categories) && empty($this->sorts)) {
            foreach ($this->categories as $cat) {
                $builder->addSort(CategorySort::class, $cat);
            }
        }

        $search = $builder->getSearch();

        $search->setQuery(
            $builder->getQuery($rank)
        );

        return $search->search();
    }

    /**
     * Find the filter class.
     *
     * @param  string  $type
     * @return mixed
     */
    private function findFilter($type)
    {
        // Is this an attribute filter?
        if ($attribute = $this->getAttribute($type)) {
            $type = $attribute->type;
        }

        $name = ucfirst(camel_case(str_singular($type))).'Filter';
        $classname = "GetCandy\Api\Core\Search\Providers\Elastic\Filters\\{$name}";

        if (class_exists($classname)) {
            return app()->make($classname);
        } else {
            return app()->make("GetCandy\Api\Core\Search\Providers\Elastic\Filters\TextFilter");
        }
    }

    /**
     * Find a matching attribute based on filter type.
     *
     * @param  string  $type
     * @return mixed
     */
    protected function getAttribute($type)
    {
        return $this->builder->getAttributes()->firstWhere('handle', $type);
    }
}
