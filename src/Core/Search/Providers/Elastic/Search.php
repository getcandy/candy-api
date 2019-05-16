<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Query;
use Elastica\Suggest;
use Elastica\Query\Wildcard;
use GetCandy\Api\Core\Search\ClientContract;
use GetCandy\Api\Core\Search\Providers\Elastic\Sorts\CategorySort;

class Search implements ClientContract
{
    /**
     * The Search Builder.
     *
     * @var SearchBuilder
     */
    protected $builder;

    protected $aggregators = [
        'priceRange',
    ];

    /**
     * @var FilterSet
     */
    protected $filterSet;

    protected $categories = [];

    protected $sorts = [];

    /**
     * The filters to apply to the search.
     *
     * @var null|array|Collection
     */
    protected $filters = null;

    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Set the user on the search.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @return self
     */
    public function user($user = null)
    {
        $this->builder->setUser($user);

        return $this;
    }

    /**
     * Set the keywords on the builder.
     *
     * @param string $keywords
     * @return Search
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
     * @param array $sorts
     * @return Search
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
     * @param int $page
     * @return Search
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
     * @return Search
     */
    public function on($channel = null)
    {
        $this->builder->setChannel($channel);

        return $this;
    }

    /**
     * Set the index.
     *
     * @param string $index
     * @return Search
     */
    public function against($type)
    {
        $this->builder->setType($type);

        return $this;
    }

    /**
     * Set the categories.
     *
     * @param array $categories
     * @return Search
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
     * @param array $filters
     * @return void
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
     * @param string $lang
     * @return self
     */
    public function language($lang = 'en')
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Set the suggestions.
     *
     * @param string $keywords
     * @return void
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
     * @param string $sku
     * @param int $limit
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
     * @param  string $keywords
     *
     * @return array
     */
    public function search($rank = true)
    {
        $roles = app('api')->roles()->getHubAccessRoles();

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
     * @param string $type
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
     * @param string $type
     * @return mixed
     */
    protected function getAttribute($type)
    {
        return $this->builder->getAttributes()->firstWhere('handle', $type);
    }
}
