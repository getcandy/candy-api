<?php

namespace GetCandy\Api\Http\Controllers\Search;

use Illuminate\Http\Request;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Products\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Search\SearchRequest;
use GetCandy\Api\Core\Channels\Services\ChannelService;
use GetCandy\Api\Core\Products\Services\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Core\Categories\Services\CategoryService;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Transformers\Fractal\Search\SearchSuggestionTransformer;

class SearchController extends BaseController
{
    /**
     * @var \GetCandy\Api\Core\Channels\Services\ChannelService
     */
    protected $channels;

    /**
     * @var \GetCandy\Api\Core\Products\Services\ProductService
     */
    protected $products;

    public function __construct(ChannelService $channels, CategoryService $categories, ProductService $products)
    {
        $this->channels = $channels;
        $this->categories = $categories;
        $this->products = $products;
    }

    /**
     * Performs a search against a type.
     *
     * @param  \GetCandy\Api\Http\Requests\Search\SearchRequest  $request
     * @param  \GetCandy\Api\Core\Search\SearchContract  $search
     * @return array|\Illuminate\Http\Response
     */
    public function search(SearchRequest $request, SearchContract $search)
    {
        // Get channel
        $defaultChannel = $this->channels->getDefaultRecord();
        $channel = $request->channel ?: ($defaultChannel ? $defaultChannel->handle : null);

        try {
            $categories = $this->categories->getByHashedIds(
                explode(':', $request->category)
            );
        } catch (ModelNotFoundException $e) {
            $categories = null;
        }

        // TODO: Deprecate current page
        if ($request->current_page) {
            $page = $request->current_page;
        } else {
            $page = $request->page;
        }

        // Get our filterable attributes.
        $filterable = app('api')->attributes()->getFilterable()->pluck('handle')->toArray();
        $filterable[] = 'price';

        try {
            $results = $search
                ->client()
                ->language(app()->getLocale())
                ->on($channel)
                ->against($request->get('search_type', 'product'))
                ->user($request->user())
                ->categories($categories)
                ->filters($request->only($filterable))
                ->sorting($request->sort)
                ->pagination($page ?: 1, $request->per_page ?: 30)
                ->keywords($request->keywords)
                ->search((bool) $request->get('rank', true));
        } catch (\Elastica\Exception\Connection\HttpException $e) {
            return $this->errorInternalError($e->getMessage());
        } catch (\Elastica\Exception\ResponseException $e) {
            return $this->errorInternalError($e->getMessage());
        }


        $ids = collect();

        if ($results->count()) {
            foreach ($results as $r) {
                $ids->push($r->getSource()['id'] ?? null);
            }
        }

        $searchResponse = $results->getResponse();
        $meta = $searchResponse->getData();

        if ($request->search_type == 'category') {
            $models = $this->categories->getSearchedIds($ids, $this->parseIncludes($request->include));
            $paginator = new LengthAwarePaginator(
                $models,
                $meta['hits']['total'],
                $results->getQuery()->getParam('size'),
                $page
            );

            $resource = new CategoryCollection($paginator);
        } else {
            $models = $this->products->getSearchedIds($ids, $this->parseIncludes($request->include));
            $paginator = new LengthAwarePaginator(
                $models,
                $meta['hits']['total'],
                $results->getQuery()->getParam('size'),
                $page
            );

            $resource = new ProductCollection($paginator);
        }

        // $results = app('api')->search()->getResults(
        //     $results,
        //     $request->get('search_type', 'product'),
        //     $request->include,
        //     $page ?: 1,
        //     $request->category,
        //     $request->user(),
        //     $request->ids_only ?: false
        // );

        return $resource->additional([
            'meta' => [
                'aggregations' => $meta['aggregations']
            ],
        ]);
    }

    /**
     * Gets suggested searches.
     *
     * @param  \GetCandy\Api\Http\Requests\Search\SearchRequest  $request
     * @param  \GetCandy\Api\Core\Search\SearchContract  $client
     * @return array
     */
    public function suggest(SearchRequest $request, SearchContract $client)
    {
        try {
            $results = $client
                ->client()
                ->language(app()->getLocale())
                ->on('webstore')
                ->against(Product::class)
                ->user($request->user())
                ->suggest($request->keywords);
        } catch (\Elastica\Exception\Connection\HttpException $e) {
            return $this->errorInternalError($e->getMessage());
        } catch (\Elastica\Exception\ResponseException $e) {
            return $this->errorInternalError($e->getMessage());
        }

        $results = app('api')->search()->getSuggestResults($results, $request->type);

        return $this->respondWithCollection($results, new SearchSuggestionTransformer);
    }

    /**
     * Handle the request to do an SKU search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \GetCandy\Api\Core\Search\SearchContract  $client
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sku(Request $request, SearchContract $client)
    {
        $this->validate($request, [
            'sku' => 'required|min:3',
        ]);

        $results = $client->client()
            ->on($request->channel)
            ->against('product')
            ->searchSkus($request->sku, $request->get('per_page', 10));

        return response()->json([
            'data' => $results,
        ]);
    }
}
