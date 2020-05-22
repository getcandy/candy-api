<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\UpdateAttributesRequest;
use GetCandy\Api\Http\Resources\Products\ProductResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductAttributeController extends BaseController
{
    /**
     * Handles the request to update a products attributes.
     * @param  string        $product
     * @param  UpdateAttributesRequest $request
     * @return mixed
     */
    public function update($product, UpdateAttributesRequest $request)
    {
        try {
            $result = app('api')->products()->updateAttributes($product, $request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new ProductResource($result);
    }
}
