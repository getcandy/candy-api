<?php

namespace GetCandy\Api\Http\Controllers\Collections;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Collections\CreateRequest;
use GetCandy\Api\Http\Requests\Collections\DeleteRequest;
use GetCandy\Api\Http\Requests\Collections\UpdateRequest;
use GetCandy\Api\Core\Collections\Criteria\CollectionCriteria;
use GetCandy\Api\Http\Resources\Collections\CollectionResource;
use GetCandy\Api\Http\Resources\Collections\CollectionCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CollectionController extends BaseController
{
    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request, CollectionCriteria $criteria)
    {
        $collection = $criteria->include($request->includes)->limit(
            $request->per_page
        )->get();
        return new CollectionCollection($collection);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id, Request $request, CollectionCriteria $criteria)
    {
        try {
            $collection = $criteria->id($id)->include($request->includes)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new CollectionResource($collection);
    }

    /**
     * Handles the request to create a new channel.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        $result = app('api')->collections()->create($request->all());

        return new CollectionResource($result);
    }

    /**
     * Handles the request to update  a channel.
     * @param  string        $id
     * @param  UpdateRequest $request
     * @return Json
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->collections()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new CollectionResource($result);
    }

    /**
     * Handles the request to delete a channel.
     * @param  string        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $result = app('api')->collections()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
