<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Filters;

use Elastica\Query\Term;
use Elastica\Query\Match;
use Elastica\Query\Nested;
use Elastica\Query\BoolQuery;
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

        return $this;
    }

    public function getQuery()
    {
        $filter = new BoolQuery;

        foreach ($this->getCustomerGroups() as $model) {
            $nested = new Nested;
            $nested->setPath('customer_groups');

            $bool = new BoolQuery;

            $groupIdMatch = new Match;
            $groupIdMatch->setField('customer_groups.id', $model->encodedId());

            $visibleMatch = new Match;
            $visibleMatch->setField('customer_groups.visible', true);

            $bool->addMust($groupIdMatch);
            $bool->addMust($visibleMatch);

            $nested->setQuery($bool);

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
                $groups = $this->user->customer->customerGroups;
            }
        } else {
            $groups = [FetchDefaultCustomerGroup::run()];
        }

        return $groups;
    }
}
