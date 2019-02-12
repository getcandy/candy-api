<?php

namespace GetCandy\Api\Core\Routes;

use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\AbstractCriteria;

class RouteCriteria extends AbstractCriteria
{
    /**
     * The route path.
     *
     * @var string
     */
    protected $path;

    /**
     * The route slug.
     *
     * @var string
     */
    protected $slug;

    /**
     * Gets the underlying builder for the query.
     *
     * @return \Illuminate\Database\Eloquent\QueryBuilder
     */
    public function getBuilder()
    {
        $route = new Route;
        $builder = $route->with($this->includes ?: []);

        if ($this->path) {
            $builder->wherePath($this->path);
        }

        if ($this->slug) {
            $builder->whereSlug($this->slug);
        }

        if ($this->id) {
            $builder->where('id', '=', $route->decodeId($this->id));

            return $builder;
        }

        return $builder;
    }
}
