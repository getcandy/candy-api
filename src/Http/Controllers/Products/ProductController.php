<?php

namespace GetCandy\Api\Http\Controllers\Products;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Exceptions\InvalidLanguageException;
use GetCandy\Api\Http\Requests\Products\CreateRequest;
use GetCandy\Api\Http\Requests\Products\DeleteRequest;
use GetCandy\Api\Http\Requests\Products\UpdateRequest;
use GetCandy\Exceptions\MinimumRecordRequiredException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductRecommendationTransformer;

class ProductController extends BaseController
{
    /**
     * Handles the request to show all products.
     * @param  Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $paginator = app('api')->products()->getPaginatedData(
            $request->channel,
            $request->per_page,
            $request->current_page ?: $request->page,
            $request->ids
        );

        return $this->respondWithCollection($paginator, new ProductTransformer);
    }

    /**
     * Handles the request to show a product based on hashed ID.
     * @param  string $id
     * @return array|\Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $product = app('api')->products()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            // If it cannot be found by ID, try get the variant by SKU
            $variant = app('api')->productVariants()->getBySku($id);

            $product = app('api')->products()->getByHashedId(
                $variant->product->encodedId()
            );
            if (! $variant) {
                return $this->errorNotFound();
            }
        }

        return $this->respondWithItem($product, new ProductTransformer);
    }

    public function recommended(Request $request)
    {
        $request->validate([
            'basket_id' => 'required|hashid_is_valid:baskets',
        ]);

        // Get the recommended products based on this basket.
        $basket = app('api')->baskets()->getByHashedId($request->basket_id);

        $products = $basket->lines->map(function ($line) {
            return $line->variant->product_id;
        })->toArray();

        $recommendations = app('api')->products()->getRecommendations($products);

        return $this->respondWithCollection($recommendations, new ProductRecommendationTransformer);
    }

    /**
     * Handles the request to create a new product.
     * @param  CreateRequest $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        try {
            $result = app('api')->products()->create($request->all());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return $this->respondWithItem($result, new ProductTransformer);
    }

    /**
     * Handles the request to update a product.
     * @param  string        $id
     * @param  UpdateRequest $request
     * @return array|\Illuminate\Http\Response
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->products()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return $this->respondWithItem($result, new ProductTransformer);
    }

    /**
     * Handles the request to delete a product.
     * @param  string        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function destroy($product, DeleteRequest $request)
    {
        try {
            $result = app('api')->products()->delete($product);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
