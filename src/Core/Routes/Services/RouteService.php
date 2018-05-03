<?php

namespace GetCandy\Api\Core\Routes\Services;

use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Exceptions\MinimumRecordRequiredException;

class RouteService extends BaseService
{
    public function __construct()
    {
        $this->model = new Route;
    }

    /**
     * Gets a route by a given slug.
     * @param  string $slug
     * @return Route
     */
    public function getBySlug($slug)
    {
        $route = $this->model->where('slug', '=', $slug)->firstOrFail();
        app()->setLocale($route->locale);

        return $route;
    }

    public function update($hashedId, array $data)
    {
        $model = $this->getByHashedId($hashedId);
        $model->slug = $data['slug'];
        $model->default = $data['default'];
        $model->save();

        return $model;
    }

    public function slugExists($slug)
    {
        return $this->model->where('slug', '=', $slug)->exists();
    }

    /**
     * @param $hashedId
     * @return mixed
     * @throws MinimumRecordRequiredException
     */
    public function delete($hashedId)
    {
        $route = $this->getByHashedId($hashedId);
        if (! $route) {
            abort(404);
        }
        if ($route->element->routes->count() == 1) {
            throw new MinimumRecordRequiredException(
                trans('response.error.minimum_record')
            );
        }

        if ($route->default) {
            $newDefault = $route->element->routes->where('default', '=', false)->first();
            $newDefault->default = true;
            $newDefault->save();
        }

        return $route->delete();
    }

    /**
     * Gets a new suggested default model.
     * @return mixed
     */
    public function getNewSuggestedDefault()
    {
        return $this->model->where('default', '=', false)->where('enabled', '=', true)->first();
    }

    public function uniqueSlug($slug)
    {
        return ! ($this->model->where('slug', $slug)->exists());
    }
}
