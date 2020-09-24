<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy;
use GetCandy\Api\Exceptions\InvalidLanguageException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\ProductVariants\CreateRequest;
use GetCandy\Api\Http\Requests\ProductVariants\DeleteRequest;
use GetCandy\Api\Http\Requests\ProductVariants\UpdateRequest;
use GetCandy\Api\Http\Resources\Products\ProductResource;
use GetCandy\Api\Http\Resources\Products\ProductVariantCollection;
use GetCandy\Api\Http\Resources\Products\ProductVariantResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductVariantController extends BaseController
{
    /**
     * Handles the request to show all product variants.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Products\ProductVariantCollection
     */
    public function index(Request $request)
    {
        $paginator = GetCandy::productVariants()->getPaginatedData($request->per_page);

        return new ProductVariantCollection($paginator);
    }

    /**
     * Handles the request to show a product variant based on hashed ID.
     *
     * @param  string  $id
     * @return array|\GetCandy\Api\Http\Resources\Products\ProductVariantResource
     */
    public function show($id)
    {
        try {
            $variant = GetCandy::productVariants()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ProductVariantResource($variant);
    }

    /**
     * Handles the request to create the variants.
     *
     * @param  string  $product
     * @param  \GetCandy\Api\Http\Requests\ProductVariants\CreateRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Products\ProductResource
     */
    public function store($product, CreateRequest $request)
    {
        try {
            $result = GetCandy::productVariants()->create($product, $request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new ProductResource($result);
    }

    /**
     * Handles the request to update a product variant.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\ProductVariants\UpdateRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Products\ProductVariantResource
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = GetCandy::productVariants()->update($id, $request->all());
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
     * Handles the request to delete a product variant.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\ProductVariants\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            GetCandy::productVariants()->delete($id);
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
            $result = GetCandy::productVariants()->updateInventory($variant, $request->inventory);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ProductVariantResource($result);
    }
}
