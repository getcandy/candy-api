<?php

namespace GetCandy\Api\Core\Routes\Services;

use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;

class RouteService extends BaseService
{
    public function __construct()
    {
        $this->model = new Route;
    }

    /**
     * Gets a route by a given slug.
     *
     * @param  string  $slug
     * @return \GetCandy\Api\Core\Routes\Models\Route
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
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

    public function slugExists($slug, $path = null)
    {
        $query = $this->model->where('slug', '=', $slug);

        if ($path) {
            $query = $query->where('path', '=', $path);
        }

        return $query->exists();
    }

    /**
     * @param  string  $hashedId
     * @return bool
     *
     * @throws \GetCandy\Api\Exceptions\MinimumRecordRequiredException
     */
    public function delete($hashedId)
    {
        $route = $this->getByHashedId($hashedId, true);
        if (! $route) {
            abort(404);
        }
        // if ($route->element->routes->count() == 1) {
        //     throw new MinimumRecordRequiredException(
        //         trans('getcandy::exceptions.minimum_record_required')
        //     );
        // }

        // if ($route->default) {
        //     $newDefault = $route->element->routes->where('default', '=', false)->first();
        //     $newDefault->default = true;
        //     $newDefault->save();
        // }

        return $route->delete();
    }

    /**
     * Gets a new suggested default model.
     *
     * @return \GetCandy\Api\Core\Routes\Models\Route
     */
    public function getNewSuggestedDefault()
    {
        return $this->model->where('default', '=', false)->where('enabled', '=', true)->first();
    }

    public function uniqueSlug($slug, $path = null)
    {
        return ! ($this->model->where('slug', $slug)->where('path', $path)->exists());
    }
}
