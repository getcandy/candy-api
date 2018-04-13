<?php

namespace GetCandy\Api\Http\Controllers\Products;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\UpdateCategoriesRequest;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTransformer;

class ProductCategoryController extends BaseController
{
    /**
     * Handles the request to update a products categories.
     * @param  string        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function update($product, UpdateCategoriesRequest $request)
    {
        $categories = app('api')->productCategories()->update($product, $request->all());

        return $this->respondWithCollection($categories, new CategoryTransformer);
    }

    /**
     * Deletes a products category.
     * @param  int        $productId
     * @param  int        $categoryId
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($productId, $categoryId)
    {
        $result = app('api')->productCategories()->delete($productId, $categoryId);

        if ($result) {
            return response()->json([
                'message' => 'Successfully removed category from product',
                'categoryName' => 'test',
            ], 202);
        }

        return response()->json('Error', 500);
    }
}
