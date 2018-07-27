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
use GetCandy\Api\Core\Search\Providers\Elastic\Sorts\CategorySort;
use GetCandy\Api\Core\Search\Providers\Elastic\Filters\ChannelFilter;

class Search implements ClientContract
{
    use InteractsWithIndex;

    protected $categories = [];
    protected $channel = null;
    protected $authUser = null;

    /**
     * The Search Builder
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

    public function __construct(
        FilterSet $filterSet,
        AggregationSet $aggregationSet,
        SearchBuilder $builder
    ) {
        $this->filterSet = $filterSet;
        $this->aggregationSet = $aggregationSet;
        $this->builder = $builder;

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
        $this->builder->setUser($user);
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
     * Set the index
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
        $roles = app('api')->roles()->getHubAccessRoles();

        $builder = $this->builder
            ->setTerm($keywords)
            ->setLimit($perPage)
            ->setOffset(($page - 1) * $perPage)
            ->setSorting($sorts)
            ->withAggregations()
            ->useCustomerFilters();

        if ($category) {
            $builder->addSort(CategorySort::class, $category);
        }

        if ($builder->getUser() && ! $builder->getUser()->hasAnyRole($roles)) {
            $builder->addFilter(
                new ChannelFilter($builder->getChannel())
            );
        }

        foreach ($filters ?? [] as $filter => $value) {
            $object = $this->findFilter($filter);
            if ($object && $object = $object->process($value, $filter)) {
                $builder->addFilter($object);
            }
        }

        $search = $builder->getSearch();
        $query = $builder->getQuery();

        // if ($categoryFilter = $this->filterSet->getFilter('categories')) {
        //     $query->setPostFilter(
        //         $categoryFilter->getQuery()
        //     );
        // }


        $query->setHighlight(
            $this->getHighlight()
        );

        // $query->addAggregation(
        //     $this->getCategoryPreAgg()
        // );

        // $query->addAggregation(
        //     $this->getCategoryPostAgg()
        // );

        if ($keywords) {
            $query->setSuggest(
                $this->getSuggest($keywords)
            );
        }

        // foreach ($this->aggregationSet->get() as $agg) {
        //     $query->addAggregation(
        //         $agg->getQuery($search, $query)
        //     );
        // }

        $search->setQuery($query);

        $results = $search->search();

        return $results;
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
        }
    }

    /**
     * Find a matching attribute based on filter type
     *
     * @param string $type
     * @return mixed
     */
    protected function getAttribute($type)
    {
        return $this->builder->getAttributes()->firstWhere('handle', $type);
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
}
