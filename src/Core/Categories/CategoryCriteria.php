<?php

namespace GetCandy\Api\Core\Categories;

use GetCandy\Api\Core\Categories\Models\Category;

class CategoryCriteria
{
    /**
     * The id of the category we want to narrow down
     *
     * @var string
     */
    protected $id;

    /**
     * The array of ids to query for
     *
     * @var array
     */
    protected $ids = [];

    /**
     * The default depth to query for
     *
     * @var string|integer
     */
    protected $depth = 1;

    /**
     * Whether we show results as full tree
     *
     * @var boolean
     */
    protected $tree = false;

    /**
     * The eager loaded includes
     *
     * @var array
     */
    protected $includes = [];

    /**
     * Set a limit to the number of resources returned
     */
    protected $limit;

    public function __call($field, $arguments)
    {
        $method = 'set' . ucfirst($field);
        if (method_exists($this, $method)) {
            $this->{$method}(...$arguments);
        } else if (property_exists($this, $field)) {
            if (count($arguments) <= 1) {
                $this->{$field} = $arguments[0] ?? null;
            } else {
                $this->{$field} = $arguments;
            }
        }
        return $this;
    }


    /**
     * Set the includes to eager load
     *
     * @param array|string $arrayOrString
     * @return void
     */
    public function include($arrayOrString = [])
    {
        if (is_string($arrayOrString)) {
            $arrayOrString = explode(',', $arrayOrString);
        }
        $this->includes = $arrayOrString;
        return $this;
    }

    /**
     * Gets the underlying builder for the query
     *
     * @return \Illuminate\Database\Eloquent\QueryBuilder
     */
    public function getBuilder()
    {
        $category = new Category;

        $builder = $category->with($this->includes ?: []);
        if ($this->id) {
            $builder->where('id', '=', $category->decodeId($this->id));
            return $builder;
        }

        if ($this->depth) {
            $builder->defaultOrder()
                ->withDepth()
                ->having('depth', '<=', $this->depth);
        }
        return $builder;
    }

    /**
     * Get the results
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        $results = $this->getBuilder()->get();
        return $this->tree ? $results->toTree() : $results;
    }

    /**
     * Get the first result from the query
     *
     * @return \Illuminate\Support\Collection
     */
    public function first()
    {
        return $this->getBuilder()->first();
    }
}