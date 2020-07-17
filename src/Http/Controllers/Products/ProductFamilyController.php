<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy;
use GetCandy\Api\Core\Products\Criteria\ProductFamilyCriteria;
use GetCandy\Api\Exceptions\InvalidLanguageException;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\ProductFamilies\CreateRequest;
use GetCandy\Api\Http\Requests\ProductFamilies\DeleteRequest;
use GetCandy\Api\Http\Requests\ProductFamilies\UpdateRequest;
use GetCandy\Api\Http\Resources\Products\ProductFamilyCollection;
use GetCandy\Api\Http\Resources\Products\ProductFamilyResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductFamilyController extends BaseController
{
    /**
     * Handles the request to show all product families.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Products\ProductFamilyCollection
     */
    public function index(Request $request)
    {
        $paginator = GetCandy::productFamilies()->getPaginatedData(
            $request->per_page,
            $request->page ?: 1,
            $this->parseIncludes($request->includes),
            $request->keywords
        );
        // event(new ViewProductEvent(['hello' => 'there']));
        return new ProductFamilyCollection($paginator);
    }

    /**
     * Handles the request to show a product family based on hashed ID.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @param  \GetCandy\Api\Core\Products\Criteria\ProductFamilyCriteria  $criteria
     * @return array|\GetCandy\Api\Http\Resources\Products\ProductFamilyResource
     */
    public function show($id, Request $request, ProductFamilyCriteria $criteria)
    {
        try {
            $family = $criteria->id($id)->includes($this->parseIncludes($request->includes))->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ProductFamilyResource($family);
    }

    /**
     * Handles the request to create a new product family.
     *
     * @param  \GetCandy\Api\Http\Requests\ProductFamilies\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        try {
            $productFamily = GetCandy::productFamilies()->create($request->all());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return new ProductFamilyResource($productFamily);
    }

    /**
     * Handles the request to update a product family.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\ProductFamilies\UpdateRequest  $request
     * @return array
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $productFamily = GetCandy::productFamilies()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return new ProductFamilyResource($productFamily);
    }

    /**
     * Handles the request to delete a product family.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\ProductFamilies\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            GetCandy::productFamilies()->delete($id, $request->product_family_id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
