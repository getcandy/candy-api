<?php

namespace GetCandy\Api\Http\Controllers\Attributes;

use Illuminate\Http\Request;
use GetCandy\Exceptions\DuplicateValueException;
use GetCandy\Api\Http\Controllers\BaseController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use GetCandy\Api\Http\Requests\AttributeGroups\CreateRequest;
use GetCandy\Api\Http\Requests\AttributeGroups\DeleteRequest;
use GetCandy\Api\Http\Requests\AttributeGroups\UpdateRequest;
use GetCandy\Api\Http\Requests\AttributeGroups\ReorderRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Resources\Attributes\AttributeGroupResource;
use GetCandy\Api\Http\Resources\Attributes\AttributeGroupCollection;
use GetCandy\Api\Http\Transformers\Fractal\Attributes\AttributeGroupTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AttributeGroupController extends BaseController
{
    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request)
    {
        $includes = $request->include ? explode(',', $request->include) : null;
        if ($request->all_records) {
            $results = app('api')->attributeGroups()->all($includes);
        } else {
            $results = app('api')->attributeGroups()->getPaginatedData($request->per_page, $request->page ?: 1, $includes);
        }
        return new AttributeGroupCollection($results);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id, Request $request)
    {
        $includes = $request->include ? explode(',', $request->include) : null;
        try {
            $attributeGroup = app('api')->attributeGroups()->getByHashedId($id, $includes);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new AttributeGroupResource($attributeGroup);
    }

    /**
     * Handles the request to create a new channel.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        $includes = $request->include ? explode(',', $request->include) : null;
        $result = app('api')->attributeGroups()->create($request->all(), $includes);

        return new AttributeGroupResource($result);
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
            $result = app('api')->attributeGroups()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new AttributeGroupResource($result);
        return $this->respondWithItem($result, new AttributeGroupTransformer);
    }

    public function reorder(ReorderRequest $request)
    {
        try {
            $result = app('api')->attributeGroups()->updateGroupPositions($request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (DuplicateValueException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        return $this->respondWithNoContent();
    }

    /**
     * Handles the request to delete a channel.
     * @param  string        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function destroy($id, Request $request)
    {
        try {
            $result = app('api')
            ->attributeGroups()
            ->delete(
                $id,
                $request->group_id,
                $request->delete_attributes
            );
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->setStatusCode($e->getStatusCode())->respondWithError($e->getMessage());
        }

        return $this->respondWithNoContent();
    }
}
