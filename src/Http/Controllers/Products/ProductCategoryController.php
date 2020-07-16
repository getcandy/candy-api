<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\UpdateCategoriesRequest;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTransformer;
use Illuminate\Http\Request;

class ProductCategoryController extends BaseController
{
    /**
     * Handles the request to update a products categories.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Products\UpdateCategoriesRequest  $request
     * @return array
     */
    public function update($product, UpdateCategoriesRequest $request)
    {
        $categories = GetCandy::productCategories()->update($product, $request->all());

        return $this->respondWithCollection($categories, new CategoryTransformer);
    }

    /**
     * Deletes a product's category.
     *
     * @param  string  $productId
     * @param  string  $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($productId, $categoryId)
    {
        $result = GetCandy::productCategories()->delete($productId, $categoryId);

        if ($result) {
            return response()->json([
                'message' => 'Successfully removed category from product',
                'categoryName' => 'test',
            ], 202);
        }

        return response()->json('Error', 500);
    }
}
