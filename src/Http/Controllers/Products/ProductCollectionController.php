<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\UpdateCollectionsRequest;
use GetCandy\Api\Http\Resources\Collections\CollectionCollection;

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
            $collections = GetCandy::productCollections()->update($product, $request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new CollectionCollection($collections);
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
        $result = GetCandy::productCollections()->delete($productId, $collectionId);

        if ($result) {
            return response()->json([
                'message' => 'Successfully removed collection from product',
            ], 202);
        }

        return response()->json('Error', 500);
    }
}
