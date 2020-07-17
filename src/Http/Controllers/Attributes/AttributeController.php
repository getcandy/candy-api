<?php

namespace GetCandy\Api\Http\Controllers\Attributes;

use GetCandy;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Attributes\CreateRequest;
use GetCandy\Api\Http\Requests\Attributes\DeleteRequest;
use GetCandy\Api\Http\Requests\Attributes\ReorderRequest;
use GetCandy\Api\Http\Requests\Attributes\UpdateRequest;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Attributes\AttributeResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AttributeController extends BaseController
{
    /**
     * Returns a listing of attributes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Attributes\AttributeCollection
     */
    public function index(Request $request)
    {
        // $attributes = GetCandy::attributes()->getPaginatedData($request->per_page);
        $attributes = new Attribute;

        if ($request->handle) {
            $attributes = $attributes->handle($request->handle);
        }

        $paginate = true;

        $attributes = $attributes->with(['group']);

        if ($request->exists('paginated') && ! $request->paginated) {
            $paginate = false;
        }

        return new AttributeCollection($paginate ? $attributes->paginate($request->per_page) : $attributes->get());
    }

    /**
     * Handles the request to show an attribute based on it's hashed ID.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Attributes\AttributeResource
     */
    public function show($id, Request $request)
    {
        $includes = $request->include ? explode(',', $request->include) : null;
        try {
            $attribute = GetCandy::attributes()->getByHashedId($id, $includes);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new AttributeResource($attribute);
    }

    /**
     * Handles the request to create a new attribute.
     *
     * @param  \GetCandy\Api\Http\Requests\Attributes\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Attributes\AttributeResource
     */
    public function store(CreateRequest $request)
    {
        $result = GetCandy::attributes()->create($request->all());

        return new AttributeResource($result);
    }

    public function reorder(ReorderRequest $request)
    {
        try {
            GetCandy::attributes()->reorder($request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (DuplicateValueException $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        return $this->respondWithNoContent();
    }

    /**
     * Handles the request to update an attribute.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Attributes\UpdateRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Attributes\AttributeResource
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = GetCandy::attributes()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new AttributeResource($result);
    }

    /**
     * Handles the request to delete an attribute.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Attributes\DeleteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            GetCandy::attributes()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
