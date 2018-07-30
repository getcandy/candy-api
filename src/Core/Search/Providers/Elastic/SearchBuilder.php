<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Query;
use Elastica\Search;
use Elastica\Client;
use Elastica\Query\BoolQuery;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Search\Providers\Elastic\Query\Term;
use GetCandy\Api\Core\Attributes\Services\AttributeService;
use GetCandy\Api\Core\Search\Providers\Elastic\Sorts\TextSort;
use GetCandy\Api\Core\Search\Providers\Elastic\Sorts\BasicSort;
use GetCandy\Api\Core\Search\Providers\Elastic\Sorts\NestedSort;
use GetCandy\Api\Core\Search\Providers\Elastic\Filters\CustomerGroupFilter;
use GetCandy\Api\Core\Search\Providers\Elastic\Aggregators\Attribute as AttributeAggregator;

class SearchBuilder
{
    use InteractsWithIndex;

    /**
     * The index to search on
     *
     * @var string
     */
    protected $index;

    /**
     * The channel to search on
     *
     * @var string
     */
    protected $channel;

    /**
     * The search type
     *
     * @var mixed
     */
    protected $type;

    /**
     * The search filters
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The filterable fields
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The applied aggregations
     *
     * @var array
     */
    protected $aggregations = [];

    /**
     * The applied sorts
     *
     * @var array
     */
    protected $sorts = [];

    /**
     * The page limit
     *
     * @var integer
     */
    protected $limit = 30;

    /**
     * The current search page
     *
     * @var integer
     */
    protected $offset = 0;

    /**
     * The search term
     *
     * @var Term
     */
    protected $term = null;

    /**
     * The user to restrict searching
     *
     * @var mixed
     */
    protected $user;

    public function __construct(AttributeService $attributes)
    {
        $this->filters = collect($this->filters);
        $this->aggregations = collect($this->aggregations);
        $this->sorts = collect($this->sorts);
        $this->client = new Client();
        $this->attributes = $attributes->all();
    }

    /**
     * Sets the search index
     *
     * @param string $index
     * @return SearchBuilder
     */
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * Set the search term
     *
     * @param string $term
     * @return SearchBuilder
     */
    public function setTerm($term)
    {
        $this->term = new Term($term, $this->type->rankings());
        return $this;
    }

    /**
     * Add a filter to the search
     *
     * @param mixed $filter
     * @param boolean $post Whether this is a post filter
     * @return SearchBuilder
     */
    public function addFilter($filter, $post = true)
    {
        $this->filters->push([
            'filter' => $filter,
            'post' => $post
        ]);
        return $this;
    }

    /**
     * Set the channel to search on
     *
     * @param string $channel
     * @return SearchBuilder
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Set the search limit
     *
     * @param integer $limit
     * @return SeachBuilder
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the search offset
     *
     * @param integar $offset
     * @return SearchBuilder
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Set the user
     *
     * @param mixed $user
     * @return SearchBuilder
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Init customer group filters
     *
     * @return SearchBuilder
     */
    public function useCustomerFilters()
    {
        $this->addFilter(
            new CustomerGroupFilter($this->user),
            false
        );
        return $this;
    }

    /**
     * Set the type
     *
     * @param string $type
     * @return SearchBuilder
     */
    public function setType($type)
    {
        switch ($type) {
            case 'product':
                $this->type = $this->getType(Product::class);
                break;
            case 'category':
                $this->type = $this->getType(Category::class);
                break;
            default:
                # code...
                break;
        }

        $this->index = $this->getCurrentIndex();

        return $this;
    }

    /**
     * Add a sort to the builder
     *
     * @param mixed $type
     * @param mixed $payload
     * @return SearchBuilder
     */
    public function addSort($type, $payload)
    {
        $this->sorts->push(
            new $type($payload)
        );
        return $this;
    }

    /**
     * Get the user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the channel
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set up aggregations based on our attributes
     *
     * @return SearchBuilder
     */
    public function withAggregations()
    {
        $aggs = array_merge(
            ['priceRange'],
            $this->attributes->where('filterable', true)->pluck('handle')->toArray()
        );

        foreach ($aggs as $agg) {
            $name = ucfirst(camel_case(str_singular($agg)));
            $classname = "GetCandy\Api\Core\Search\Providers\Elastic\Aggregators\\{$name}";
            if (class_exists($classname)) {
                $class = app()->make($classname);
            } else {
                $class = new AttributeAggregator($agg);
            }
            $this->aggregations->push($class);
        }

        return $this;
    }

    /**
     * Get the attributes
     *
     * @return Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the sorting on the search
     *
     * @param array $sorts
     * @return SearchBuilder
     */
    public function setSorting($sorts = [])
    {
        $mapping = $this->type->getMapping();

        $sortables = [];

        foreach ($sorts as $field => $dir) {

            $column = $field;

            if ($field == 'min_price' || $field == 'max_price') {
                $field = 'pricing';
            }

            if (empty($mapping[$field])) {
                continue;
            }

            $map = $mapping[$field];
            // If it's a text property, elastic won't sort on it.
            // So lets find any sortable fields we can use...
            if ($map['type'] == 'text') {
                if (empty($map['fields'])) {
                    continue;
                }
                $this->sorts->push(
                    new TextSort($field, 'sortable', $dir)
                );
            } elseif ($map['type'] == 'nested') {
                $this->sorts->push(
                    new NestedSort($field, $column, $dir, 'min')
                );
            } else {
                $sort = new BasicSort($field);
                $sort->setDir($dir);
                $this->sorts->push($sort);
            }
        }
        return $this;
    }

    /**
     * Add an aggregation to the builder
     *
     * @param GetCandy\Api\Core\Search\Providers\Elastic\Aggregators\AbstractAggregator $aggregation
     * @return SearchBuilder
     */
    public function addAggregation($aggregation)
    {
        $this->aggregations->push($aggregation);
        return $this;
    }

    /**
     * Get any fields that shouldnt be searched on
     *
     * @return array
     */
    protected function getExcludedFields()
    {
        $filterable = $this->attributes
            ->filter(function ($attribute) {
                return !$attribute->searchable && !$attribute->filterable ||$attribute->filterable;
            })
            ->pluck('handle')
            ->toArray();

        return [
            'excludes' => $filterable
        ];
    }


    /**
     * Get the search object
     *
     * @return Search
     */
    public function getSearch()
    {
        $search = new Search($this->client);
        return $search
            ->addIndex($this->index)
            ->addType(
                $this->type->getHandle()
            );
    }

    /**
     * Get the search query
     *
     * @return void
     */
    public function getQuery()
    {
        $query = new Query();
        $query->setParam('size', $this->limit);
        $query->setParam('from', $this->offset);

        $boolQuery = new BoolQuery;

        $boolQuery->addMust(
            $this->term->getQuery()
        );

        $query->setSource(
            $this->getExcludedFields()
        );

        foreach ($this->filters as $filter) {
            $boolQuery->addFilter(
                $filter->getQuery()
            );
        }

        foreach ($this->aggregations as $agg) {
            $query->addAggregation($agg->getQuery(
                $this->getSearch(),
                $query
            ));
        }

        $query->setQuery($boolQuery);

        return $query;
    }


    public function search()
    {
        $search = $this->getSearch();
        $query = $this->getQuery();

        dd($query);
        dd('hit');
    }
}
