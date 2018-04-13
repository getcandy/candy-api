<?php

namespace GetCandy\Api\Http\Controllers\Products;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use GetCandy\Api\Http\Requests\Products\UpdateAttributesRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;

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

        return $this->respondWithItem($result, new ProductTransformer);
    }
}
