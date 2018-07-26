<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Query;
use Elastica\Client;
use Elastica\Suggest;
use Elastica\Query\Term;
use Elastica\Query\Match;
use Elastica\Suggest\Phrase;
use Elastica\Query\BoolQuery;
use Elastica\Aggregation\Terms;
use Elastica\Query\Nested as NestedQuery;
use GetCandy\Api\Core\Search\ClientContract;
use Elastica\Aggregation\Filter as FilterAggregation;
use Elastica\Aggregation\Nested as NestedAggregation;
use Elastica\Suggest\CandidateGenerator\DirectGenerator;

class Search implements ClientContract
{
    use InteractsWithIndex;

    protected $categories = [];
    protected $channel = null;
    protected $authUser = null;

    protected $aggregators = [
        'priceRange',
    ];

    /**
     * @var FilterSet
     */
    protected $filterSet;

    public function __construct(
        Client $client,
        FilterSet $filterSet,
        AggregationSet $aggregationSet
    ) {
        $this->client = $client;
        $this->filterSet = $filterSet;
        $this->aggregationSet = $aggregationSet;

        foreach ($this->aggregators as $agg) {
            $this->aggregationSet->add($agg);
        }
    }

    public function with($searchterm)
    {
        return $this->search($searchterm);
    }

    /**
     * Set the user on the search
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @return self
     */
    public function user($user = null)
    {
        $this->authUser = $user;

        return $this;
    }

    /**
     * Set the channel to filter on.
     *
     * @return void
     */
    public function on($channel = null)
    {
        if (! $channel) {
            $this->setChannelDefault();
        } else {
            $this->channel = $channel;
        }

        return $this;
    }

    /**
     * Set the search language
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
     * Set the search channel
     *
     * @return self
     */
    protected function setChannelDefault()
    {
        $channel = app('api')->channels()->getDefaultRecord()->handle;
        $this->channel = $channel;

        return $this;
    }

    /**
     * Set the suggestions
     *
     * @param string $keywords
     * @return void
     */
    public function suggest($keywords)
    {
        if (! $this->channel) {
            $this->setChannelDefault();
        }

        $search = new \Elastica\Search($this->client);
        $search
            ->addIndex($this->getSearchIndex())
            ->addType($this->type->getHandle());

        $suggest = new \Elastica\Suggest;
        $term = new \Elastica\Suggest\Completion('suggest', 'name.suggest');
        $term->setText($keywords);
        $suggest->addSuggestion($term);

        return $search->search($suggest);
    }

    /**
     * Searches the index.
     *
     * @param  string $keywords
     *
     * @return array
     */
    public function search($keywords, $category = null, $filters = [], $sorts = [], $page = 1, $perPage = 25)
    {
        if (! $this->type) {
            abort(400, 'You need to set an indexer first');
        }

        $roles = app('api')->roles()->getHubAccessRoles();
        $user = app('auth')->user();

        if (! $this->channel) {
            $this->setChannelDefault();
        }

        $search = new \Elastica\Search($this->client);
        $search
            ->addIndex($this->getSearchIndex())
            ->addType($this->type->getHandle());

        $query = new Query();
        $query->setParam('size', $perPage);
        $query->setParam('from', ($page - 1) * $perPage);

        foreach ($this->getSorts($sorts) as $sortable) {
            $query->addSort($sortable);
        }

        if ($category && empty($sorts)) {
            $query->setSort(
                $this->getDefaultCategorySort($category)
            );
        }

        $boolQuery = new BoolQuery;

        if ($keywords) {
            $disMaxQuery = $this->generateDisMax($keywords);
            $boolQuery->addMust($disMaxQuery);
        }

        $filter = $this->getCustomerGroupFilter();

        $boolQuery->addFilter($filter);

        if ($user && ! $user->hasAnyRole($roles)) {
            $boolQuery->addFilter(
                $this->getChannelFilter()
            );
        }

        foreach ($filters ?? [] as $filter => $value) {
            $this->filterSet->add($filter, $value);
        }

        if ($category) {
            $this->filterSet->add('categories', $category);
        }

        foreach ($this->filterSet->getFilters() as $filter) {
            if ($filterQuery = $filter->getQuery()) {
                $boolQuery->addFilter($filterQuery);
            }
        }

        if ($categoryFilter = $this->filterSet->getFilter('categories')) {
            $query->setPostFilter(
                $categoryFilter->getQuery()
            );
        }

        $query->setQuery($boolQuery);

        $query->setHighlight(
            $this->getHighlight()
        );

        $query->addAggregation(
            $this->getCategoryPreAgg()
        );

        $query->addAggregation(
            $this->getCategoryPostAgg()
        );

        // dd($query);

        if ($keywords) {
            $query->setSuggest(
                $this->getSuggest($keywords)
            );
        }

        foreach ($this->aggregationSet->get() as $agg) {
            $query->addAggregation(
                $agg->getQuery($search, $query)
            );
        }

        $search->setQuery($query);

        $results = $search->search();

        return $results;
    }

    /**
     * Set the filters on search
     *
     * @param array $payload
     * @return void
     */
    public function setFilters($payload = [])
    {
        $this->filterSet->add($payload);
        return $this;
    }

    /**
     * Get the suggester.
     *
     * @return Suggest
     */
    protected function getSuggest($keywords)
    {
        // Did you mean...
        $phrase = new Phrase(
            'name',
            'name'
        );
        $phrase->setGramSize(3);
        $phrase->setSize(1);
        $phrase->setText($keywords);

        $generator = new DirectGenerator('name');
        $generator->setSuggestMode('always');
        $generator->setField('name');
        $phrase->addCandidateGenerator($generator);

        $phrase->setHighlight('<strong>', '</strong>');
        $suggest = new Suggest;
        $suggest->addSuggestion($phrase);

        return $suggest;
    }

    /**
     * Gets the category post aggregation.
     *
     * @return NestedAggregation
     */
    protected function getCategoryPostAgg()
    {
        $nestedAggPost = new NestedAggregation(
            'categories_after',
            'departments'
        );

        $agg = new FilterAggregation('categories_after_filter');

        // Add boolean
        $postBool = new BoolQuery();

        foreach ($this->categories as $category) {
            $term = new Term;
            $term->setTerm('departments.id', $category);
            $postBool->addMust($term);
        }

        // Need to set another agg on categories_remaining
        $childAgg = new \Elastica\Aggregation\Terms('categories_post_inner');
        $childAgg->setField('departments.id');

        // Do the terms in the categories loop...
        $agg->setFilter($postBool);
        $agg->addAggregation($childAgg);

        $nestedAggPost->addAggregation($agg);

        return $nestedAggPost;
    }

    /**
     * Returns the category before aggregation.
     *
     * @return NestedAggregation
     */
    protected function getCategoryPreAgg()
    {
        // Get our category aggregations
        $nestedAggBefore = new NestedAggregation(
            'categories_before',
            'departments'
        );

        $childAgg = new \Elastica\Aggregation\Terms('categories_before_inner');
        $childAgg->setField('departments.id');

        $nestedAggBefore->addAggregation($childAgg);

        return $nestedAggBefore;
    }

    /**
     * Gets the highlight for the search query.
     *
     * @return array
     */
    protected function getHighlight()
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

    private function getRealCategoryIds($categories)
    {
        return app('api')->categories()->getDecodedIds($categories['values']);
    }

    /**
     * Gets the default sorting data for categories.
     *
     * @param array $categories
     *
     * @return array
     */
    protected function getDefaultCategorySort($category)
    {
        $category = app('api')->categories()->getByHashedId($category);

        $defaultSort = [];

        if ($category->sort == 'custom') {
            $sort = [
                'departments.position' => [
                    'order' => 'asc',
                    'mode' => 'max',
                    'nested_path' => 'departments',
                    'nested_filter' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    'departments.id' => $category->encodedId(),
                                ],
                            ],
                        ],
                    ],
                ],
            ];
            $defaultSort[] = $sort;
        } else {
            $sort = explode(':', $category->sort);
            $defaultSort = $this->getSorts([$sort[0] => $sort[1]]);
        }

        return $defaultSort;
    }

    private function getCustomerGroups()
    {
        if ($user = $this->authUser) {
            // Set to empty array as we don't want to filter any out.
            if ($user->hasRole('admin')) {
                $groups = [];
            } else {
                $groups = $user->groups;
            }
        } else {
            $groups = [app('api')->customerGroups()->getGuest()];
        }

        return $groups;
    }

    protected function getCustomerGroupFilter()
    {
        $filter = new BoolQuery;

        foreach ($this->getCustomerGroups() as $model) {
            $cat = new NestedQuery();
            $cat->setPath('customer_groups');
            $term = new Term;
            $term->setTerm('customer_groups.id', $model->encodedId());

            $cat->setQuery($term);

            $filter->addShould($cat);
        }

        return $filter;
    }

    /**
     * Get the channel filter
     *
     * @return BoolQuery
     */
    protected function getChannelFilter()
    {
        $filter = new BoolQuery;

        $cat = new NestedQuery();
        $cat->setPath('channels');
        $term = new Term;
        $term->setTerm('channels.handle', $this->channel);

        $cat->setQuery($term);

        $filter->addMust($cat);

        return $filter;
    }

    /**
     * Generates the DisMax query.
     *
     * @param string $keywords
     *
     * @return void
     */
    protected function generateDisMax($keywords)
    {
        $disMaxQuery = new \Elastica\Query\DisMax();
        $disMaxQuery->setBoost(1.5);
        $disMaxQuery->setTieBreaker(1);

        $multiMatchQuery = new \Elastica\Query\MultiMatch();
        $multiMatchQuery->setType('phrase');
        $multiMatchQuery->setQuery($keywords);
        $multiMatchQuery->setFields($this->type->rankings());

        $disMaxQuery->addQuery($multiMatchQuery);

        $multiMatchQuery = new \Elastica\Query\MultiMatch();
        $multiMatchQuery->setType('best_fields');
        $multiMatchQuery->setQuery($keywords);

        $multiMatchQuery->setFields($this->type->rankings());

        $disMaxQuery->addQuery($multiMatchQuery);

        return $disMaxQuery;
    }

    /**
     * Gets an array of mapped sortable fields.
     *
     * @param array $sorts
     *
     * @return arrau
     */
    protected function getSorts($sorts = [])
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
                foreach ($map['fields'] as $handle => $subField) {
                    if ($subField['type'] != 'text') {
                        $sortables[] = [$field.'.'.$handle => $dir];
                    }
                }
            } elseif ($map['type'] == 'nested') {
                $column = $field.'.'.str_replace('_price', '', $column);
                $sort = [
                    $column => [
                        'order' => $dir,
                        'mode' => 'min',
                        'nested_path' => 'pricing',
                        'nested_filter' => [
                            'bool' => [
                                'must' => [],
                            ],
                        ],
                    ],
                ];
                foreach ($this->getCustomerGroups() as $group) {
                    $sort[$column]['nested_filter']['bool']['must'] = [
                        'match' => [
                            $field.'.id' => $group->encodedId(),
                        ],
                    ];
                }
                $sortables[] = $sort;
            } else {
                $sortables[] = [$field => $dir];
            }
        }

        return $sortables;
    }
}
