<?php

namespace GetCandy\Api\Search\Elastic;

use Elastica\Aggregation\Filter as FilterAggregation;
use Elastica\Aggregation\Nested as NestedAggregation;
use Elastica\Aggregation\Terms;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\Nested as NestedQuery;
use Elastica\Query\Term;
use Elastica\Suggest;
use Elastica\Suggest\CandidateGenerator\DirectGenerator;
use Elastica\Suggest\Phrase;
use GetCandy\Api\Search\ClientContract;

class Search extends AbstractProvider implements ClientContract
{
    protected $categories = [];
    protected $channel = null;
    protected $authUser = null;

    public function with($searchterm)
    {
        return $this->search($searchterm);
    }

    protected function getSearchIndex($indexer)
    {
        return config('search.index_prefix') . $this->lang;
    }

    public function user($user = null)
    {
        $this->authUser = $user;
        return $this;
    }

    /**
     * Set the channel to filter on
     *
     * @return void
     */
    public function on($channel = null)
    {
        if (!$channel) {
            $this->setChannelDefault();
        } else {
            $this->channel = $channel;
        }
        return $this;
    }

    protected function setChannelDefault()
    {
        $channel = app('api')->channels()->getDefaultRecord()->handle;
        $this->channel = $channel;
        return $this;
    }

    /**
     * Searches the index
     *
     * @param  string $keywords
     *
     * @return array
     */
    public function search($keywords, $filters = [], $sorts = [], $page = 1, $perPage = 25)
    {
        if (!$this->indexer) {
            abort(400, 'You need to set an indexer first');
        }

        $roles = app('api')->roles()->getHubAccessRoles();
        $user = app('auth')->user();

        if (!$this->channel) {
            $this->setChannelDefault();
        }

        $search = new \Elastica\Search($this->client);
        $search
            ->addIndex($this->indexer->getIndexName())
            ->addType($this->indexer->type);

        $query = new Query();
        $query->setParam('size', $perPage);
        $query->setParam('from', ($page - 1) * $perPage);

        foreach ($this->getSorts($sorts) as $sortable) {
            $query->addSort($sortable);
        }

        $boolQuery = new BoolQuery;

        if ($keywords) {
            $disMaxQuery = $this->generateDisMax($keywords);
            $boolQuery->addMust($disMaxQuery);
        }

        $filter = $this->getCustomerGroupFilter();

        $boolQuery->addFilter($filter);

        if ($user && !$user->hasAnyRole($roles)) {
            $boolQuery->addFilter(
                $this->getChannelFilter()
            );
        }

        if (!empty($filters['categories'])) {
            $categories = $filters['categories']['values'];
            $filter = $this->getCategoryFilter($categories);

            $boolQuery->addFilter(
                $filter
            );
            $query->setPostFilter(
                $filter
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

        $search->setQuery($query);

        $results = $search->search();
        return $results;
    }

    /**
     * Get the suggester
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
     * Gets the category post aggregation
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
     * Returns the category before aggregation
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
     * Gets the highlight for the search query
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

    /**
     * Gets the category post filter
     *
     * @param array $categories
     *
     * @return void
     */
    protected function getCategoryFilter($categories = [])
    {
        $filter = new BoolQuery;

        foreach ($categories as $value) {
            $cat = new NestedQuery();
            $cat->setPath('departments');

            $term = new Term;
            $term->setTerm('departments.id', $value);

            $cat->setQuery($term);

            $filter->addMust($cat);
            $this->categories[] = $value;
        }

        return $filter;
    }

    protected function getCustomerGroupFilter()
    {
        $filter = new BoolQuery;

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

        foreach ($groups as $model) {
            $cat = new NestedQuery();
            $cat->setPath('customer_groups');
            $term = new Term;
            $term->setTerm('customer_groups.id', $model->encodedId());

            $cat->setQuery($term);

            $filter->addMust($cat);
        }

        return $filter;
    }

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
     * Generates the DisMax query
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
        $multiMatchQuery->setFields($this->indexer->rankings());

        $disMaxQuery->addQuery($multiMatchQuery);

        $multiMatchQuery = new \Elastica\Query\MultiMatch();
        $multiMatchQuery->setType('best_fields');
        $multiMatchQuery->setQuery($keywords);

        $multiMatchQuery->setFields($this->indexer->rankings());

        $disMaxQuery->addQuery($multiMatchQuery);

        return $disMaxQuery;
    }

    /**
     * Gets an array of mapped sortable fields
     *
     * @param array $sorts
     *
     * @return arrau
     */
    protected function getSorts($sorts = [])
    {
        $mapping = $this->indexer->mapping();

        $sortables = [];

        foreach ($sorts as $field => $dir) {
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
                        $sortables[] = [$field . '.' . $handle => $dir];
                    }
                }
            } else {
                $sortables[] = [$field => $dir];
            }
        }

        return $sortables;
    }
}
