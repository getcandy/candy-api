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
use GetCandy\Api\Http\Transformers\Fractal\Attributes\AttributeGroupTransformer;

class AttributeGroupController extends BaseController
{
    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request)
    {
        $paginator = app('api')->attributeGroups()->getPaginatedData($request->per_page);

        return $this->respondWithCollection($paginator, new AttributeGroupTransformer);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $attribute = app('api')->attributeGroups()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($attribute, new AttributeGroupTransformer);
    }

    /**
     * Handles the request to create a new channel.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        $result = app('api')->attributeGroups()->create($request->all());

        return $this->respondWithItem($result, new AttributeGroupTransformer);
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
