<?php

namespace GetCandy\Api\Http\Controllers\Products;

use Illuminate\Http\Request;
use GetCandy\Api\Core\Products\ProductCriteria;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Exceptions\InvalidLanguageException;
use GetCandy\Api\Http\Requests\Products\CreateRequest;
use GetCandy\Api\Http\Requests\Products\DeleteRequest;
use GetCandy\Api\Http\Requests\Products\UpdateRequest;
use GetCandy\Exceptions\MinimumRecordRequiredException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Products\DuplicateRequest;
use GetCandy\Api\Http\Resources\Products\ProductResource;
use Symfony\Component\HttpKernel\Exception\HttpException;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Core\Baskets\Interfaces\BasketCriteriaInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;
use GetCandy\Api\Http\Resources\Products\ProductRecommendationCollection;
use GetCandy\Api\Core\Products\Factories\ProductDuplicateFactory;
use GetCandy\Api\Core\Products\Models\Product;

class ProductController extends BaseController
{
    /**
     * Handles the request to show all products.
     * @param  Request $request
     * @return array
     */
    public function index(Request $request, ProductCriteria $criteria)
    {
        $products = $criteria
            ->include($request->includes)
            ->ids($request->ids)
            ->limit($request->get('limit', 50))
            ->get();

        return new ProductCollection($products, $this->parseIncludedFields($request));
    }

    /**
     * Handles the request to show a product based on hashed ID.
     * @param  string $id
     * @return array|\Illuminate\Http\Response
     */
    public function show($id, Request $request, ProductCriteria $criteria)
    {
        $product = $criteria
            ->include($request->includes)
            ->id($id)
            ->first();

        if (! $product) {
            $product = $criteria->blank('id')->sku($id)->first();
        }

        if (! $product) {
            return $this->errorNotFound();
        }
        $resource = new ProductResource($product);

        return $resource->only($request->fields);
    }

    public function recommended(Request $request, ProductCriteria $productCriteria, BasketCriteriaInterface $baskets)
    {
        $request->validate([
            'basket_id' => 'required|hashid_is_valid:baskets',
        ]);

        $basket = $baskets->id($request->basket_id)->first();

        $products = $basket->lines->map(function ($line) {
            return $line->variant->product_id;
        })->toArray();

        $products = app('api')->products()->getRecommendations($products);

        return new ProductRecommendationCollection($products);
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

    public function duplicate($product, DuplicateRequest $request, ProductDuplicateFactory $factory)
    {
        try {
            $product = Product::with([
                'variants',
                'routes',
                'assets',
                'customerGroups',
                'channels'
            ])->findOrFail((new Product)->decodeId($product));
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }
        $result = $factory->init($product)->duplicate(collect($request->all()));

        return new ProductResource($result);
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
