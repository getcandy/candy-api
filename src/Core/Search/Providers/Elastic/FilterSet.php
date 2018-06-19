<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use GetCandy\Api\Core\Search\Providers\Elastic\Filters\FilterAbstract;

class FilterSet
{
    protected $filters = [];

    public function __construct()
    {
        $this->filters = collect();
    }

    /**
     * Add a filter to the chain
     *
     * @param string $type
     * @param mixed $payload
     * @return self
     */
    public function add($type, $payload)
    {
        $filter = $this->findFilter($type);

        if ($filter && $filter = $filter->process($payload)) {
            $this->filters->put($type, $filter);
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get a filter from the chain
     *
     * @param string $handle
     * @return mixed
     */
    public function getFilter($handle)
    {
        return $this->filters[$handle] ?? null;
    }

    /**
     * Find the filter class
     *
     * @param string $type
     * @return mixed
     */
    private function findFilter($type)
    {
        $name = ucfirst(camel_case(str_singular($type))) . 'Filter';
        $classname = "GetCandy\Api\Core\Search\Providers\Elastic\Filters\\{$name}";
        if (class_exists($classname)) {
            return app()->make($classname);
        }
        return null;
    }

}
