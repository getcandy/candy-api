<?php

namespace GetCandy\Api\Http\Controllers\Products;

use DB;
use Drafting;
use GetCandy\Api\Core\Baskets\Interfaces\BasketCriteriaInterface;
use GetCandy\Api\Core\Products\Factories\ProductDuplicateFactory;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\ProductCriteria;
use GetCandy\Api\Core\Products\Services\ProductService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\CreateRequest;
use GetCandy\Api\Http\Requests\Products\DeleteRequest;
use GetCandy\Api\Http\Requests\Products\DuplicateRequest;
use GetCandy\Api\Http\Requests\Products\UpdateRequest;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Http\Resources\Products\ProductRecommendationCollection;
use GetCandy\Api\Http\Resources\Products\ProductResource;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;
use GetCandy\Exceptions\InvalidLanguageException;
use GetCandy\Exceptions\MinimumRecordRequiredException;
use Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends BaseController
{
    /**
     * The product service
     *
     * @var ProductService
     */
    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    /**
     * Handles the request to show all products.
     * @param  Request $request
     * @return array
     */
    public function index(Request $request, ProductCriteria $criteria)
    {
        $paginate = true;

        if ($request->exists('paginated') && !$request->paginated) {
            $paginate = false;
        }

        $products = $criteria
            ->include($request->include)
            ->ids($request->ids)
            ->limit($request->get('limit', 50))
            ->paginated($paginate)
            ->get();

        return new ProductCollection($products);
    }

    /**
     * Handles the request to show a product based on hashed ID.
     * @param  string $id
     * @return array|\Illuminate\Http\Response
     */
    public function show($idOrSku, Request $request)
    {
        $id = Hashids::connection('product')->decode($idOrSku);


        $includes = $request->include ?: [];

        if ($includes && is_string($includes)) {
            $includes = explode(',', $includes);
        }


        if (empty($id[0])) {
            $product = $this->service->findBySku($idOrSku, $includes, $request->draft);
        } else {
            $product = $this->service->findById($id[0], $includes, $request->draft);
        }

        if (! $product) {

            return $this->errorNotFound();
        }
        $resource = new ProductResource($product);

        return $resource->only($request->fields);
    }

    public function createDraft($id, Request $request)
    {
        $id = Hashids::connection('product')->decode($id);
        if (empty($id[0])) {
            return $this->errorNotFound();
        }
        $product = $this->service->findById($id[0], [], false);
        $draft = Drafting::with('products')->firstOrCreate($product);

        return new ProductResource($draft->load($request->includes));
    }

    public function publishDraft($id, Request $request)
    {
        $id = Hashids::connection('product')->decode($id);
        if (empty($id[0])) {
            return $this->errorNotFound();
        }
        $product = $this->service->findById($id[0], [], true);

        Drafting::with('products')->publish($product);

        $includes = $request->includes ? explode(',', $request->includes) : [];

        return new ProductResource($product->load($includes));
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
                'channels',
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
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $id = Hashids::connection('product')->decode($id);
            if (empty($id[0])) {
                return $this->errorNotFound();
            }
            $result = $this->service->delete($id[0], true);
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
