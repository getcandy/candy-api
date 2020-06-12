<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\UpdateCollectionsRequest;
use GetCandy\Api\Http\Transformers\Fractal\Collections\CollectionTransformer;

class ProductCollectionController extends BaseController
{
    /**
     * @param  string  $product
     * @param  \GetCandy\Api\Http\Requests\Products\UpdateCollectionsRequest  $request
     * @return array
     */
    public function update($product, UpdateCollectionsRequest $request)
    {
        try {
            $collections = app('api')->productCollections()->update($product, $request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithCollection($collections, new CollectionTransformer);
    }

    /**
     * Deletes a products collection.
     * 
     * @param  string  $productId
     * @param  string  $collectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($productId, $collectionId)
    {
        $result = app('api')->productCollections()->delete($productId, $collectionId);

        if ($result) {
            return response()->json([
                'message' => 'Successfully removed collection from product',
            ], 202);
        }

        return response()->json('Error', 500);
    }
}
