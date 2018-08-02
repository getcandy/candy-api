<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use GetCandy\Api\Core\Attributes\Services\AttributeService;

class FilterSet
{
    protected $filters = [];
    protected $filterable = [];

    public function __construct(AttributeService $attributes)
    {
        $this->filters = collect();
        $this->filterable = $attributes->getFilterable();
    }

    /**
     * Add a filter to the chain.
     *
     * @param mixed $type
     * @param mixed $payload
     * @return self
     */
    public function add($type, $payload = null)
    {
        $filter = $this->findFilter($type);

        if ($filter && $filter = $filter->process($payload, $type)) {
            $this->filters->put($type, $filter);
        }

        return $this;
    }

    /**
     * Add many filters to the search.
     *
     * @param array $filters
     * @return object
     */
    public function addMany(array $filters)
    {
        foreach ($filters as $key => $value) {
            $this->add($key, $value);
        }

        return $this;
    }

    /**
     * Undocumented function.
     *
     * @return void
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get the filterable fields.
     *
     * @return void
     */
    public function getFilterable()
    {
        return $this->filterable;
    }

    /**
     * Get a filter from the chain.
     *
     * @param string $handle
     * @return mixed
     */
    public function getFilter($handle)
    {
        return $this->filters[$handle] ?? null;
    }

    /**
     * Find a matching attribute based on filter type.
     *
     * @param string $type
     * @return mixed
     */
    protected function getAttribute($type)
    {
        return $this->filterable->firstWhere('handle', $type);
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
}
