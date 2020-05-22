<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\ProductVariants\CreateRequest;
use GetCandy\Api\Http\Requests\ProductVariants\DeleteRequest;
use GetCandy\Api\Http\Requests\ProductVariants\UpdateRequest;
use GetCandy\Api\Http\Resources\Products\ProductResource;
use GetCandy\Api\Http\Resources\Products\ProductVariantCollection;
use GetCandy\Api\Http\Resources\Products\ProductVariantResource;
use GetCandy\Exceptions\InvalidLanguageException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductVariantController extends BaseController
{
    /**
     * Handles the request to show all product families.
     * @param  Request $request
     * @return Json
     */
    public function index(Request $request)
    {
        $paginator = app('api')->productVariants()->getPaginatedData($request->per_page);

        return new ProductVariantCollection($paginator);
    }

    /**
     * Handles the request to show a product family based on hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $variant = app('api')->productVariants()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ProductVariantResource($variant);
    }

    /**
     * Handles the request to create the variants.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store($product, CreateRequest $request)
    {
        try {
            $result = app('api')->productVariants()->create($product, $request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new ProductResource($result);
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
            $result = app('api')->productVariants()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ProductVariantResource($result);
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
            $result = app('api')->productVariants()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }

    public function updateInventory($variant, Request $request)
    {
        try {
            $result = app('api')->productVariants()->updateInventory($variant, $request->inventory);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ProductVariantResource($result);
    }
}
