<?php
namespace GetCandy\Api\Search\Elastic;

use Elastica\Client;
use GetCandy\Api\Categories\Models\Category;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Search\Elastic\Indexers\CategoryIndexer;
use GetCandy\Api\Search\Elastic\Indexers\ProductIndexer;
use Illuminate\Database\Eloquent\Model;
use Elastica\Status;

abstract class AbstractProvider
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $lang = 'en';

    /**
     * @var array
     */
    protected $indexers = [
        Product::class => ProductIndexer::class,
        Category::class => CategoryIndexer::class
    ];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function language($lang = 'en')
    {
        $this->lang = $lang;
        return $this;
    }

    public function against($types)
    {
        $this->indexer = $this->getIndexer($types);
        return $this;
    }
    /**
     * Checks whether an indexer exists
     * @param  mixed  $model
     * @return boolean
     */
    public function hasIndexer($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }
        return isset($this->indexers[$model]);
    }

    /**
     * Gets the client for the model
     * @return Elastica\Client
     */
    public function client()
    {
        if (!$this->client) {
            return new Client();
        }
        return $this->client;
    }

    public function hasIndex($name)
    {
        $elasticaStatus = new Status($this->client());
        return $elasticaStatus->indexExists($name) or $elasticaStatus->aliasExists($name);
    }

    /**
     * Gets the indexer for a model
     * @param  mixed $model
     * @return mixed
     */
    public function getIndexer($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }
        if (!$this->hasIndexer($model)) {
            abort(400, "No indexer available for {$model}");
        }
        return new $this->indexers[$model];
    }
}
