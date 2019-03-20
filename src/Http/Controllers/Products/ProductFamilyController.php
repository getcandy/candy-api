<?php

namespace GetCandy\Api\Http\Controllers\Products;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Exceptions\InvalidLanguageException;
use GetCandy\Exceptions\MinimumRecordRequiredException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\ProductFamilies\CreateRequest;
use GetCandy\Api\Http\Requests\ProductFamilies\DeleteRequest;
use GetCandy\Api\Http\Requests\ProductFamilies\UpdateRequest;
use GetCandy\Api\Core\Products\Criteria\ProductFamilyCriteria;
use GetCandy\Api\Http\Resources\Products\ProductFamilyResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductFamilyTransformer;

class ProductFamilyController extends BaseController
{
    /**
     * Handles the request to show all product families.
     * @param  Request $request
     * @return Json
     */
    public function index(Request $request)
    {
        $paginator = app('api')->productFamilies()->getPaginatedData($request->per_page);
        // event(new ViewProductEvent(['hello' => 'there']));
        return $this->respondWithCollection($paginator, new ProductFamilyTransformer);
    }

    /**
     * Handles the request to show a product family based on hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id, Request $request, ProductFamilyCriteria $criteria)
    {
        try {
            $family = $criteria->id($id)->includes($request->includes)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ProductFamilyResource($family);
    }

    /**
     * Handles the request to create a new product family.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        try {
            $result = app('api')->productFamilies()->create($request->all());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return $this->respondWithItem($result, new ProductFamilyTransformer);
    }

    /**
     * Handles the request to update a product family.
     * @param  string        $id
     * @param  UpdateRequest $request
     * @return Json
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->productFamilies()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return $this->respondWithItem($result, new ProductFamilyTransformer);
    }

    /**
     * Handles the request to delete a product family.
     * @param  string        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $result = app('api')->productFamilies()->delete($id, $request->product_family_id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
