<?php

namespace GetCandy\Api\Core\Categories;

use GetCandy\Api\Core\Scaffold\AbstractCriteria;
use GetCandy\Api\Core\Categories\Models\Category;

class CategoryCriteria extends AbstractCriteria
{
    /**
     * The default depth to query for.
     *
     * @var string|int
     */
    protected $depth = 1;

    /**
     * Whether we show results as full tree.
     *
     * @var bool
     */
    protected $tree = false;

    /**
     * Gets the underlying builder for the query.
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

        if ($this->limit && ! $this->tree) {
            $builder->limit($this->limit);
        }

        if ($this->depth) {
            $builder->defaultOrder()
                ->withDepth()
                ->having('depth', '<=', $this->depth);
        }

        return $builder;
    }

    /**
     * Get the results.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        return $this->tree ? $this->getTree() : $this->getBuilder()->get();
    }

    protected function getTree()
    {
        $results = $this->getBuilder()->get();

        return $results->toTree()->take($this->limit);
    }
}
