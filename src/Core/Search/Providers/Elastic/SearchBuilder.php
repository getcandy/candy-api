<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Query;
use Elastica\Client;
use Elastica\Search;
use Elastica\Suggest;
use Elastica\Suggest\Phrase;
use Elastica\Query\BoolQuery;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Categories\Models\Category;
use Elastica\Suggest\CandidateGenerator\DirectGenerator;
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
     * The index to search on.
     *
     * @var string
     */
    protected $index;

    /**
     * The channel to search on.
     *
     * @var string
     */
    protected $channel;

    /**
     * The search type.
     *
     * @var mixed
     */
    protected $type;

    /**
     * The search filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The filterable fields.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The applied aggregations.
     *
     * @var array
     */
    protected $aggregations = [];

    /**
     * The applied sorts.
     *
     * @var array
     */
    protected $sorts = [];

    /**
     * The page limit.
     *
     * @var int
     */
    protected $limit = 30;

    /**
     * The current search page.
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * The search term.
     *
     * @var Term
     */
    protected $term = null;

    /**
     * The scoring function.
     * @var
     */
    protected $scoring = null;

    /**
     * The user to restrict searching.
     *
     * @var mixed
     */
    protected $user;

    protected $topFilters = [
        'category-filter',
        'customer-group-filter',
    ];

    public function __construct(AttributeService $attributes)
    {
        $this->filters = collect($this->filters);
        $this->aggregations = collect($this->aggregations);
        $this->sorts = collect($this->sorts);
        $this->client = new Client(config('getcandy.search.client_config.elastic', []));
        $this->attributes = $attributes->all();
    }

    /**
     * Sets the search index.
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
     * Set the search term.
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
     * Add a filter to the search.
     *
     * @param mixed $filter
     * @param bool $post Whether this is a post filter
     * @return SearchBuilder
     */
    public function addFilter($filter, $post = true)
    {
        $this->filters->push([
            'handle' => $filter->handle,
            'filter' => $filter,
            'post' => $post,
        ]);

        return $this;
    }

    /**
     * Set the channel to search on.
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
     * Set the function score.
     *
     * @param mixed $score
     * @return void
     */
    public function scoring($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Set the search limit.
     *
     * @param int $limit
     * @return SeachBuilder
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set the search offset.
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
     * Get the offset value.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set the user.
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
     * Init customer group filters.
     *
     * @return SearchBuilder
     */
    public function useCustomerFilters()
    {
        $filter = new CustomerGroupFilter;
        $filter->process($this->user);
        $this->addFilter($filter, false);

        return $this;
    }

    /**
     * Set the type.
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
                // code...
                break;
        }

        $this->index = $this->getCurrentIndex();

        return $this;
    }

    /**
     * Add a sort to the builder.
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
     * Get the user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the channel.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set up aggregations based on our attributes.
     *
     * @return SearchBuilder
     */
    public function withAggregations()
    {
        $aggs = array_merge(
            ['priceRange', 'category'],
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
     * Get the attributes.
     *
     * @return Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the sorting on the search.
     *
     * @param array $sorts
     * @return SearchBuilder
     */
    public function setSorting($sortables = null)
    {
        $sorts = [];

        if ($sortables) {
            $sortable = explode('|', $sortables);
            foreach ($sortable as $sort) {
                $segments = explode('-', $sort);
                $dir = $segments[1] ?? 'asc';
                $field = $segments[0];
                $sorts[$field] = $dir;
            }
        }

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
     * Add an aggregation to the builder.
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
     * Get any fields that shouldnt be searched on.
     *
     * @return array
     */
    protected function getExcludedFields()
    {
        $filterable = $this->attributes
            ->filter(function ($attribute) {
                return ! $attribute->searchable && ! $attribute->filterable || $attribute->filterable;
            })
            ->pluck('handle')
            ->toArray();

        return [
            'excludes' => $filterable,
        ];
    }

    /**
     * Get the search object.
     *
     * @return Search
     */
    public function getSearch()
    {
        $search = new Search($this->client);

        return $search
            ->addIndex($this->index)
            ->setOption(
                Search::OPTION_SEARCH_TYPE,
                Search::OPTION_SEARCH_TYPE_DFS_QUERY_THEN_FETCH
            )
            ->addType(
                $this->type->getHandle()
            );
    }

    /**
     * Get the search query.
     *
     * @return void
     */
    public function getQuery($rank)
    {
        $query = new Query();
        $query->setParam('size', $this->limit);
        $query->setParam('from', $this->offset);

        $boolQuery = new BoolQuery;

        if ($this->term) {
            $boolQuery->addMust(
                $this->term->getQuery()
            );
            $query->setSuggest(
                $this->getSuggest()
            );
        }

        $query->setSource(
            $this->getExcludedFields()
        );

        // Set filters as post filters
        $postFilter = new BoolQuery;

        foreach ($this->filters as $filter) {
            if (! empty($filter['post'])) {
                $postFilter->addFilter(
                    $filter['filter']->getQuery()
                );
            } else {
                $boolQuery->addFilter(
                    $filter['filter']->getQuery()
                );
            }

            if (method_exists($filter['filter'], 'aggregate')) {
                $query->addAggregation(
                    $filter['filter']->aggregate()->getPost(
                        $filter['filter']->getValue()
                    )
                );
            }
        }
        $query->setPostFilter($postFilter);

        foreach ($this->aggregations as $agg) {

            // If we have a category or customer group filter
            // then make sure the aggregation supports it.
            $topLevelFilters = $this->filters->filter(function ($filter) {
                return in_array($filter['handle'], $this->topFilters);
            });

            foreach ($topLevelFilters as $filter) {
                $agg = $agg->addFilter($filter);
            }

            $cloned = $query;
            $query->addAggregation($agg->getPre(
                $this->getSearch(),
                $cloned->setQuery($boolQuery),
                $postFilter
            ));
        }

        $query->setQuery($boolQuery);

        $query->setHighlight(
            $this->highlight()
        );

        foreach ($this->sorts as $sort) {
            $query->addSort($sort->getMapping(
                $this->user
            ));
        }

        return $query;
    }

    /**
     * Get the search highlight.
     *
     * @return array
     */
    protected function highlight()
    {
        return [
            'pre_tags' => ['<em class="highlight">'],
            'post_tags' => ['</em>'],
            'fields' => [
                'name' => [
                    'number_of_fragments' => 0,
                ],
                'description' => [
                    'number_of_fragments' => 0,
                ],
            ],
        ];
    }

    /**
     * Get the suggester.
     *
     * @return Suggest
     */
    protected function getSuggest()
    {
        // Did you mean...
        $phrase = new Phrase(
            'name',
            'name'
        );
        $phrase->setGramSize(3);
        $phrase->setSize(1);
        $phrase->setText($this->term->getText());

        $generator = new DirectGenerator('name');
        $generator->setSuggestMode('always');
        $generator->setField('name');
        $phrase->addCandidateGenerator($generator);

        $phrase->setHighlight('<strong>', '</strong>');
        $suggest = new Suggest;
        $suggest->addSuggestion($phrase);

        return $suggest;
    }
}
