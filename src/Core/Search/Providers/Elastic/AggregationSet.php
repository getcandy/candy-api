<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

class AggregationSet
{
    protected $aggregations = [];

    public function __construct()
    {
        $this->aggregations = collect();
    }

    /**
     * Add a filter to the chain.
     *
     * @param string $type
     * @param mixed $payload
     * @return self
     */
    public function add($type)
    {
        $agg = $this->findAggregation($type);

        if ($agg) {
            $this->aggregations->put($type, $agg);
        }

        return $this;
    }

    public function get()
    {
        return $this->aggregations;
    }

    /**
     * Find the filter class.
     *
     * @param string $type
     * @return mixed
     */
    private function findAggregation($type)
    {
        $name = ucfirst(camel_case(str_singular($type))).'Aggregator';
        $classname = "GetCandy\Api\Core\Search\Providers\Elastic\Aggregators\\{$name}";
        if (class_exists($classname)) {
            return app()->make($classname);
        }
    }
}
