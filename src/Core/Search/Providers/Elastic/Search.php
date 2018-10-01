<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Suggest;
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
     * Searches the index.
     *
     * @param  string $keywords
     *
     * @return array
     */
    public function search($keywords, $category = null, $filters = [], $sorts = null, $page = 1, $perPage = 30)
    {
        $roles = app('api')->roles()->getHubAccessRoles();
        $builder = $this->builder;

        if ($keywords) {
            $builder->setTerm($keywords);
        }



        $builder->setLimit($perPage)
            ->setOffset(($page - 1) * $perPage)
            ->setSorting($sorts)
            ->withAggregations()
            ->useCustomerFilters();

        if ($category) {
            $filter = $this->findFilter('Category');
            $filter->process($category);
            $builder->addFilter($filter);
        }

        if ($category && empty($sorts)) {
            foreach ($category as $cat) {
                $builder->addSort(CategorySort::class, $cat);
            }
        }

        if ($channel = $builder->getChannel()) {
            $channelFilter = $this->findFilter('Channel');
            $channelFilter->process($channel);
            $builder->addFilter($channelFilter);
        }

        foreach ($filters ?? [] as $filter => $value) {
            $object = $this->findFilter($filter);
            if ($object && $object = $object->process($value, $filter)) {
                $builder->addFilter($object);
            }
        }

        $search = $builder->getSearch();
        $query = $builder->getQuery();

        $search->setQuery($query);

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
