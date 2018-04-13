<?php

namespace GetCandy\Api\Http\Controllers\Attributes;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Attributes\CreateRequest;
use GetCandy\Api\Http\Requests\Attributes\DeleteRequest;
use GetCandy\Api\Http\Requests\Attributes\UpdateRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Attributes\ReorderRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Attributes\AttributeTransformer;

class AttributeController extends BaseController
{
    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request)
    {
        $attributes = app('api')->attributes()->getPaginatedData($request->per_page);

        return $this->respondWithCollection($attributes, new AttributeTransformer);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $attribute = app('api')->attributes()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($attribute, new AttributeTransformer);
    }

    /**
     * Handles the request to create a new channel.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        $result = app('api')->attributes()->create($request->all());

        return $this->respondWithItem($result, new AttributeTransformer);
    }

    public function reorder(ReorderRequest $request)
    {
        try {
            $result = app('api')->attributes()->reorder($request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (DuplicateValueException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        return $this->respondWithNoContent();
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
            $result = app('api')->attributes()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new AttributeTransformer);
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
            $result = app('api')->attributes()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
