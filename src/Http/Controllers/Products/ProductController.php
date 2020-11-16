<?php

namespace GetCandy\Api\Http\Controllers\Products;

use Drafting;
use GetCandy;
use GetCandy\Api\Core\Baskets\Interfaces\BasketCriteriaInterface;
use GetCandy\Api\Core\Products\Factories\ProductDuplicateFactory;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\ProductCriteria;
use GetCandy\Api\Core\Products\Services\ProductService;
use GetCandy\Api\Exceptions\InvalidLanguageException;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\CreateRequest;
use GetCandy\Api\Http\Requests\Products\DeleteRequest;
use GetCandy\Api\Http\Requests\Products\DuplicateRequest;
use GetCandy\Api\Http\Requests\Products\UpdateRequest;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Http\Resources\Products\ProductResource;
use Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends BaseController
{
    /**
     * @var \GetCandy\Api\Core\Products\Services\ProductService
     */
    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    /**
     * Handles the request to show all products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \GetCandy\Api\Core\Products\ProductCriteria  $criteria
     * @return \GetCandy\Api\Http\Resources\Products\ProductCollection
     */
    public function index(Request $request, ProductCriteria $criteria)
    {
        $paginate = true;

        if ($request->exists('paginated') && ! $request->paginated) {
            $paginate = false;
        }

        $products = $criteria
            ->include($this->parseIncludes($request->include))
            ->ids($request->ids)
            ->limit($request->get('limit', 50))
            ->paginated($paginate)
            ->get();

        return new ProductCollection($products);
    }

    /**
     * Handles the request to show a product based on hashed ID.
     *
     * @param  string  $idOrSku
     * @param  \Illuminate\Http\Request  $request
     * @return array|\GetCandy\Api\Http\Resources\Products\ProductResource
     */
    public function show($idOrSku, Request $request)
    {
        $id = Hashids::connection('product')->decode($idOrSku);

        $includes = $this->parseIncludes($request->include);

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

        return new ProductResource($draft->load($this->parseIncludes($request->include)));
    }

    public function publishDraft($id, Request $request)
    {
        $id = Hashids::connection('product')->decode($id);
        if (empty($id[0])) {
            return $this->errorNotFound();
        }
        $product = $this->service->findById($id[0], [], true);

        Drafting::with('products')->publish($product);

        return new ProductResource($product->load($this->parseIncludes($request->include)));
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

        $products = GetCandy::products()->getRecommendations($products);

        return new ProductRecommendationCollection($products);
    }

    /**
     * Handles the request to create a new product.
     *
     * @param  \GetCandy\Api\Http\Requests\Products\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        try {
            $product = GetCandy::products()->create($request->all());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return new ProductResource($product);
    }

    /**
     * Handles the request to update a product.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Products\UpdateRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $product = GetCandy::products()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return new ProductResource($product);
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
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Products\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
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
