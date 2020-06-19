<?php

namespace GetCandy\Api\Http\Controllers\Search;

use GetCandy\Api\Core\Categories\Services\CategoryService;
use GetCandy\Api\Core\Channels\Services\ChannelService;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Services\ProductService;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Search\SearchRequest;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Http\Transformers\Fractal\Search\SearchSuggestionTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $query = $results->getQuery();

        $aggregations = $search->parseAggregations($meta['aggregations']);

        $service = $this->products;
        $resource = ProductCollection::class;

        if ($request->search_type == 'category') {
            $service = $this->categories;
            $resource = CategoryCollection::class;
        }

        $models = $service->getSearchedIds($ids, $this->parseIncludes($request->include));

        $paginator = new LengthAwarePaginator(
            $models,
            $meta['hits']['total'],
            $results->getQuery()->getParam('size'),
            $page
        );

        return (new $resource($paginator))->additional([
            'meta' => [
                'aggregations' => $aggregations,
                'highlight' => $query->getParam('highlight'),
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
