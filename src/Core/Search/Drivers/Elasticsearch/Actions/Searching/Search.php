<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Search as ElasticaSearch;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Actions\FetchSearchedIds;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Action;

class Search extends Action
{
    /**
     * @var array
     */
    protected $topFilters = [
        'channel-filter',
        'customer-group-filter',
        'category-filter',
    ];


    protected $start;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'index' => 'nullable|string',
            'limit' => 'nullable|numeric',
            'offset' => 'nullable|numeric',
            'page'   => 'nullable|numeric',
            'search_type' => 'nullable|string',
            'filters' => 'nullable',
            'aggregate' => 'nullable|array',
            'term' => 'nullable|string',
            'language' => 'nullable|string',
            'page' => 'nullable|numeric|min:1',
            'category' => 'nullable|string',
        ];
    }

    /**
     * Execute the action and return a result.
     */
    public function handle()
    {
        $this->start = now();
        $this->set('search_type', $this->search_type ? Str::plural($this->search_type) : 'products');

        \Log::debug("Start: " . now()->subMillisecond($this->start->format('v'))->format('v'));

        if (! $this->index) {
            $prefix = config('getcandy.search.index_prefix');
            $language = app()->getLocale();
            $index = Str::plural($this->search_type);
            $this->set('index', "{$prefix}_{$index}_{$language}");
        }

        $this->filters = $this->filters ? collect(explode(',', $this->filters))->mapWithKeys(function ($filter) {
            [$label, $value] = explode(':', $filter);

            return [$label => $value];
        })->toArray() : [];

        $this->aggregates = $this->aggregates ?: [];
        $this->language = $this->language ?: app()->getLocale();
        $this->set('category', $this->category ? explode(':', $this->category) : []);

        \Log::debug("Building client: " . now()->subMillisecond($this->start->format('v'))->format('v'));

        $client = FetchClient::run();

        \Log::debug("Adding search term: " . now()->subMillisecond($this->start->format('v'))->format('v'));

        $term = $this->term ? FetchTerm::run($this->attributes) : null;

        \Log::debug("Fetching filters: " . now()->subMillisecond($this->start->format('v'))->format('v'));

        $filters = FetchFilters::run([
            'category' => $this->category,
            'filters' => $this->filters,
        ]);

        $limit = $this->limit ?: 100;

        $offset = (($this->page ?: 1) - 1) * $limit;

        $query = new Query();
        $query->setParam('size', $limit);
        $query->setParam('from', $offset);

        $boolQuery = new BoolQuery;

        \Log::debug("Setting term and suggestion: " . now()->subMillisecond($this->start->format('v'))->format('v'));

        if ($term) {
            $boolQuery->addMust($term);

            $query = SetSuggestion::run([
                'query' => $query,
                'term' => $this->term,
            ]);
        }

        \Log::debug("Fetching aggregations: " . now()->subMillisecond($this->start->format('v'))->format('v'));


        $aggregations = FetchAggregations::run();

        $query = SetExcludedFields::run(['query' => $query]);

        // Set filters as post filters
        $postFilter = new BoolQuery;

        \Log::debug("Applying pre filters: " . now()->subMillisecond($this->start->format('v'))->format('v'));

        $preFilters = $filters->filter(function ($filter) {
            return in_array($filter->handle, $this->topFilters);
        });

        $preFilters->each(function ($filter) use ($boolQuery) {
            // dump($filter->getQuery());
            $boolQuery->addFilter(
                 $filter->getQuery()
             );
        });

        \Log::debug("Applying post filters: " . now()->subMillisecond($this->start->format('v'))->format('v'));


        $postFilters = $filters->filter(function ($filter) {
            return ! in_array($filter->handle, $this->topFilters);
        });

        $postFilters->each(function ($filter) use ($postFilter, $query) {
            if (method_exists($filter, 'aggregate')) {
                $query->addAggregation(
                    $filter->aggregate()->getPost(
                        $filter->getValue()
                    )
                );
            }
            $postFilter->addFilter(
                $filter->getQuery()
            );
        });

        $query->setPostFilter($postFilter);

        \Log::debug("Applying aggregations: " . now()->subMillisecond($this->start->format('v'))->format('v'));


        // // $globalAggregation = new \Elastica\Aggregation\GlobalAggregation('all_products');
        foreach ($aggregations as $aggregation) {
            if (method_exists($aggregation, 'get')) {
                $query->addAggregation(
                     $aggregation->addFilters($postFilters)->get($postFilters)
                 );
                // $globalAggregation->addAggregation(
                     // $agg->addFilters($postFilters)->get($postFilters)
                 // );
            }
        }

        \Log::debug("Setting query: " . now()->subMillisecond($this->start->format('v'))->format('v'));


        $query->setQuery($boolQuery);

        \Log::debug("Set sorting: " . now()->subMillisecond($this->start->format('v'))->format('v'));

        $query = SetSorting::run([
            'query' => $query,
            'type' => $this->search_type,
            'sort' => $this->sort,
        ]);

        \Log::debug("Set highlighting: " . now()->subMillisecond($this->start->format('v'))->format('v'));


        $query->setHighlight(config('getcandy.search.highlight') ?? [
            'pre_tags' => ['<em class="highlight">'],
            'post_tags' => ['</em>'],
            'fields' => [
                '*' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 50,
                ],
            ],
        ]);

        $query = $query->setSource(false)->setStoredFields([]);

        \Log::debug("Initialising the search HTTP client: " . now()->subMillisecond($this->start->format('v'))->format('v'));


        $search = new ElasticaSearch($client);

        \Log::debug("Before ES Query: " . now()->subMillisecond($this->start->format('v'))->format('v'));
        $result = $search
            ->addIndex(
                $this->index ?: config('getcandy.search.index')
            )
            ->setOption(
                ElasticaSearch::OPTION_SEARCH_TYPE,
                ElasticaSearch::OPTION_SEARCH_TYPE_DFS_QUERY_THEN_FETCH
            )->search($query);

            \Log::debug("After ES Query: " . now()->subMillisecond($this->start->format('v'))->format('v'));

        return $result;
    }

    /**
     * @param $result
     * @param $request
     *
     * @return CategoryCollection|ProductCollection
     */
    public function jsonResponse($result, $request)
    {
        \Log::debug("Got response: " . now()->subMillisecond($this->start->format('v'))->format('v'));
        $ids = collect();
        $results = collect($result->getResults());

        if ($results->count()) {
            foreach ($results as $r) {
                $ids->push($r->getId());
            }
        }

        \Log::debug("Mapping Aggregations: " . now()->subMillisecond($this->start->format('v'))->format('v'));


        $aggregations = MapAggregations::run([
            'aggregations' => $result->getAggregations(),
        ]);

        \Log::debug("Fetching searched IDs: " . now()->subMillisecond($this->start->format('v'))->format('v'));

        $models = FetchSearchedIds::run([
            'model' => $this->search_type == 'products' ? Product::class : Category::class,
            'encoded_ids' => $ids->toArray(),
            'include' => $request->include,
            'counts' => $request->counts,
        ]);

        \Log::debug("Got IDs: " . now()->subMillisecond($this->start->format('v'))->format('v'));

        $resource = ProductCollection::class;

        if ($this->search_type == 'categories') {
            $resource = CategoryCollection::class;
        }

        $paginator = new LengthAwarePaginator(
            $models,
            $result->getTotalHits(),
            $result->getQuery()->getParam('size'),
            $this->page ?: 1
        );

        \Log::debug("Building response : " . now()->subMillisecond($this->start->format('v'))->format('v'));

        $response = (new $resource($paginator))->additional([
            'meta' => [
                'count' => $models->count(),
                'aggregations' => $aggregations,
                'highlight' => $result->getQuery()->getParam('highlight'),
            ],
        ]);

        return $response;
    }
}
