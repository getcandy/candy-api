<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

use Elastica\Query\BoolQuery;
use Elastica\Query\Nested;
use Elastica\Query\Term;
use GetCandy\Api\Core\Customers\Actions\FetchDefaultCustomerGroup;

class CustomerGroupFilter extends AbstractFilter
{
    /**
     * The user.
     *
     * @var mixed
     */
    protected $user;

    public $handle = 'customer-group-filter';

    protected $field = 'customer-groups';

    public function process($payload, $type = null)
    {
        $this->user = $payload;
    }

    public function getQuery()
    {
        $filter = new BoolQuery;

        foreach ($this->getCustomerGroups() as $model) {
            $nested = new Nested;
            $nested->setPath('customer_groups');
            $term = new Term;
            $term->setTerm('customer_groups.id', $model->encodedId());

            $nested->setQuery($term);

            $filter->addShould($nested);
        }

        return $filter;
    }

    private function getCustomerGroups()
    {
        if ($this->user) {
            // Set to empty array as we don't want to filter any out.
            if ($this->user->hasRole('admin')) {
                $groups = [];
            } else {
                $groups = $this->user->groups;
            }
        } else {
            $groups = [FetchDefaultCustomerGroup::run()];
        }

        return $groups;
    }
}
