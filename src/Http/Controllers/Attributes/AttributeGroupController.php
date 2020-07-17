<?php

namespace GetCandy\Api\Http\Controllers\Attributes;

use GetCandy;
use GetCandy\Api\Exceptions\DuplicateValueException;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\AttributeGroups\CreateRequest;
use GetCandy\Api\Http\Requests\AttributeGroups\ReorderRequest;
use GetCandy\Api\Http\Requests\AttributeGroups\UpdateRequest;
use GetCandy\Api\Http\Resources\Attributes\AttributeGroupCollection;
use GetCandy\Api\Http\Resources\Attributes\AttributeGroupResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AttributeGroupController extends BaseController
{
    /**
     * Returns a listing of attribute groups.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Attributes\AttributeGroupCollection
     */
    public function index(Request $request)
    {
        $includes = $request->include ? explode(',', $request->include) : null;
        if ($request->all_records) {
            $results = GetCandy::attributeGroups()->all($includes);
        } else {
            $results = GetCandy::attributeGroups()->getPaginatedData($request->per_page, $request->page ?: 1, $includes);
        }

        return new AttributeGroupCollection($results);
    }

    /**
     * Handles the request to show an attribute group based on it's hashed ID.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Attributes\AttributeGroupResource
     */
    public function show($id, Request $request)
    {
        $includes = $request->include ? explode(',', $request->include) : null;
        try {
            $attributeGroup = GetCandy::attributeGroups()->getByHashedId($id, $includes);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new AttributeGroupResource($attributeGroup);
    }

    /**
     * Handles the request to create a new attribute group.
     *
     * @param  \GetCandy\Api\Http\Requests\AttributeGroups\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Attributes\AttributeGroupResource
     */
    public function store(CreateRequest $request)
    {
        $includes = $request->include ? explode(',', $request->include) : null;
        $result = GetCandy::attributeGroups()->create($request->all(), $includes);

        return new AttributeGroupResource($result);
    }

    /**
     * Handles the request to update an attribute group.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\AttributeGroups\UpdateRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Attributes\AttributeGroupResource
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = GetCandy::attributeGroups()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new AttributeGroupResource($result);
    }

    public function reorder(ReorderRequest $request)
    {
        try {
            GetCandy::attributeGroups()->updateGroupPositions($request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (DuplicateValueException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        return $this->respondWithNoContent();
    }

    /**
     * Handles the request to delete an attribute group.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\AttributeGroups\DeleteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        try {
            GetCandy::attributeGroups()->delete(
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
