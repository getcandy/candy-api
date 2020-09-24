<?php

namespace GetCandy\Api\Http\Controllers\Routes;

use GetCandy;
use GetCandy\Api\Core\Routes\RouteCriteria;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Routes\UpdateRequest;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Routes\RouteResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteController extends BaseController
{
    public function index()
    {
        $routes = GetCandy::routes()->getPaginatedData();

        return new RouteCollection($routes);
    }

    /**
     * Handles the request to show a route based on it's hashed ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \GetCandy\Api\Core\Routes\RouteCriteria  $routes
     * @return array|\GetCandy\Api\Http\Resources\Routes\RouteResource
     */
    public function show(Request $request, RouteCriteria $routes)
    {
        try {
            $route = $routes->slug($request->slug)->path($request->path)->includes($request->include)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new RouteResource($route);
    }

    /**
     * Update a route.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Routes\UpdateRequest  $request
     * @return array
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $route = GetCandy::routes()->update($id, $request->all());
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new RouteResource($route);
    }

    public function destroy($id)
    {
        try {
            GetCandy::routes()->delete($id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }
    }
}
