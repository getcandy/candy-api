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
            'search_type' => 'nullable|string',
            'filters' => 'nullable|array',
            'aggregate' => 'nullable|array',
            'term' => 'nullable|string',
            'language' => 'nullable|string',
            'page' => 'nullable|numeric|min:1',
        ];
    }

    /**
     * Execute the action and return a result.
     */
    public function handle()
    {
        $this->set('search_type', $this->search_type ?: 'products');

        if (! $this->index) {
            $prefix = config('getcandy.search.index_prefix');
            $language = app()->getLocale();

            $this->set('index', "{$prefix}_{$this->search_type}_{$language}");
        }

        $this->filters = $this->filters ?: [];
        $this->aggregates = $this->aggregates ?: [];
        $this->language = $this->language ?: app()->getLocale();

        $client = FetchClient::run();

        $term = $this->term ? FetchTerm::run($this->attributes) : null;
        $filters = $this->delegateTo(FetchFilters::class);

        $query = new Query();
        $query->setParam('size', $this->limit ?: 100);
        $query->setParam('from', $this->offset ?: 0);

        $boolQuery = new BoolQuery;

        if ($term) {
            $boolQuery->addMust($term);

            $query = SetSuggestion::run([
                'query' => $query,
                'term' => $this->term,
            ]);
        }

        $aggregations = $this->delegateTo(FetchAggregations::class) ?? [];

        $query = SetExcludedFields::run(['query' => $query]);

        // Set filters as post filters
        $postFilter = new BoolQuery;

        $preFilters = $filters->filter(function ($filter) {
            return in_array($filter->handle, $this->topFilters);
        });

        $preFilters->each(function ($filter) use ($boolQuery) {
            $boolQuery->addFilter(
                 $filter->getQuery()
             );
        });

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

        $query->setQuery($boolQuery);

        $query = SetSorting::run([
            'query' => $query,
            'type' => $this->search_type,
            'sort' => $this->sort,
        ]);

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

        $search = new ElasticaSearch($client);

        return $search
            ->addIndex(
                $this->index ?: config('getcandy.search.index')
            )
            ->setOption(
                ElasticaSearch::OPTION_SEARCH_TYPE,
                ElasticaSearch::OPTION_SEARCH_TYPE_DFS_QUERY_THEN_FETCH
            )->search($query);
    }

    /**
     * @param $result
     * @param $request
     *
     * @return CategoryCollection|ProductCollection
     */
    public function jsonResponse($result, $request)
    {
        $ids = collect();
        $results = collect($result->getResults());

        if ($results->count()) {
            foreach ($results as $r) {
                $ids->push($r->getId());
            }
        }

        $models = FetchSearchedIds::run([
            'model' => $this->search_type == 'products' ? Product::class : Category::class,
            'encoded_ids' => $ids->toArray(),
            'include' => $request->include,
        ]);

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

        return (new $resource($paginator))->additional([
            'meta' => [
                'aggregations' => $result->getAggregations(),
                'highlight' => $result->getQuery()->getParam('highlight'),
            ],
        ]);
    }
}
