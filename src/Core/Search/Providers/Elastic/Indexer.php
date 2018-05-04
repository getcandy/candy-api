<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Client;
use Elastica\Status;
use Elastica\Reindex;
use Elastica\Document;
use Elastica\Type\Mapping;
use GetCandy\Api\Search\IndexContract;
use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Search\Providers\Elastic\Types\ProductType;
use GetCandy\Api\Core\Search\Providers\Elastic\Types\CategoryType;

class Indexer
{
    /**
     * @var Client
     */
    protected $client;

    protected $type = null;

    protected $suffix = null;

    protected $batch = 0;

    /**
     * @var array
     */
    protected $types = [
        Product::class => ProductType::class,
        Category::class => CategoryType::class,
    ];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Gets the base index name
     *
     * @return string
     */
    protected function getBaseIndexName()
    {
        return config('getcandy.search.index_prefix') . '_' . $this->type->getHandle();
    }

    /**
     * Gets the name of the default index
     *
     * @return string
     */
    protected function getDefaultIndex()
    {
        $defaultLang = app('api')->languages()->getDefaultRecord();
        return $this->getBaseIndexName() . "_{$defaultLang->lang}";
    }

    /**
     * Updates the mapping for a model
     *
     * @param Model $model
     * @return void
     */
    public function updateMapping($model)
    {
        $this->type = $this->getType($model);
        $indexName = $this->getDefaultIndex();
        $baseIndex = $this->getBaseIndexName();
        $currentSuffix = $this->getCurrentIndexSuffix($indexName);
        $nextSuffix = $currentSuffix == 'a' ? 'b' : 'a';
        $languages = app('api')->languages()->all();

        $aliases = [];

        foreach ($languages as $language) {

            $indexBasename = $baseIndex . "_{$language->lang}";

            $currentIndex =  $this->client->getIndex($indexBasename . "_{$currentSuffix}");

            $newIndex = $this->createIndex(
                $indexBasename . "_{$nextSuffix}"
            );

            $reindexer = new Reindex($currentIndex, $newIndex);

            $reindexer->run();

            $aliases[] = $indexBasename;
        }

        $this->cleanup($nextSuffix, $aliases);

        return true;
    }

    /**
     * Reindexes all indexes for a model
     *
     * @param string $model
     * @return void
     */
    public function indexAll($model)
    {
        $this->type = $this->getType($model);

        $languages = app('api')->languages()->all();

        $indexName = $this->getDefaultIndex();

        $model = new $model;

        $suffix = $this->getNextIndexSuffix($indexName);

        $aliases = [];

        // Go through our languages and create a new index at the correct version
        foreach ($languages as $language) {
            $alias = $this->getBaseIndexName() . '_' . $language->lang;
            $this->createIndex(
                $alias . "_{$suffix}"
            );
            $aliases[] = $alias;
        }

        // Do it in batches of 200
        $models = $model->withoutGlobalScopes()->limit(1000)->offset($this->batch)->get();

        $this->type->setSuffix($suffix);

        while ($models->count()) {
            $indexes = [];

            foreach ($models as $model) {
                $indexables = $this->type->getIndexDocument($model);
                echo '.';
                foreach ($indexables as $indexable) {
                    $document = new Document(
                        $indexable->getId(),
                        $indexable->getData()
                    );
                    $indexes[$indexable->getIndex()][] = $document;
                }
            }

            foreach ($indexes as $key => $documents) {
                $index =  $this->client->getIndex($key);
                $elasticaType = $index->getType($this->type->getHandle());
                $elasticaType->addDocuments($documents);
            }

            $elasticaType->addDocuments($documents);
            $elasticaType->getIndex()->refresh();

            echo ':batch:' . $this->batch;
            $this->batch += 1000;
            $models = $model->withoutGlobalScopes()->limit(1000)->offset($this->batch)->get();
        }

        $this->cleanup($suffix, $aliases);

        return true;
    }

    /**
     * Cleans up the indexes for next time
     *
     * @param string $suffix
     * @param array $aliases
     * @return void
     */
    private function cleanup($suffix, $aliases)
    {
        if ($suffix == 'a') {
            $remove = 'b';
        } else {
            $remove = 'a';
        }
        foreach ($aliases as $alias) {
            $index = $this->client->getIndex($alias . "_{$suffix}");
            $index->addAlias($alias);
            $this->reset($alias . "_{$remove}");
        }
    }

    /**
     * Get the suffix of the current index
     *
     * @param string $name
     * @return string
     */
    protected function getCurrentIndexSuffix($name)
    {
        return $this->getNextIndexSuffix($name) == 'a' ? 'b' : 'a';
    }

    /**
     * Get the next suffix
     *
     * @param string $name
     * @return string
     */
    protected function getNextIndexSuffix($name)
    {
        if ($this->hasIndex($name . '_a')) {
            return 'b';
        }
        return 'a';
    }

    /**
     * Determines if the index exists in elastic
     *
     * @param string $name
     * @return boolean
     */
    public function hasIndex($name)
    {
        $elasticaStatus = new Status($this->client);
        return $elasticaStatus->indexExists($name) || $elasticaStatus->aliasExists($name);
    }

    /**
     * Get the type for a model
     *
     * @param Model|string $model
     * @throws Symfony\Component\HttpKernel\Exception\HttpException;
     * @return mixed
     */
    public function getType($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }
        if (!$this->hasType($model)) {
            abort(400, "No type available for {$model}");
        }
        return new $this->types[$model];
    }

    /**
     * Checks whether an indexer exists.
     * @param  mixed  $model
     * @return bool
     */
    public function hasType($model)
    {
        if (is_object($model)) {
            $model = get_class($model);
        }
        return isset($this->types[$model]);
    }

    /**
     * Index a single object
     *
     * @param Model $model
     * @return void
     */
    public function indexObject(Model $model)
    {
        $this->type = $this->getType($model);
        $indexName = $this->getDefaultIndex();
        if (!$this->suffix) {
            $this->suffix = $this->getCurrentIndexSuffix($indexName);
        }
        return $this->addToIndex($model, $this->suffix);
    }

    /**
     * Add a single model to the elastic index
     *
     * @param Model $model
     * @param string $suffix
     * @return boolean
     */
    protected function addToIndex(Model $model, $suffix = null)
    {
        $type = $this->type->setSuffix($suffix);

        $indexables = $type->getIndexDocument($model);

        foreach ($indexables as $indexable) {


            $index =  $this->client->getIndex(
                $indexable->getIndex()
            );

            $elasticaType = $index->getType($this->type->getHandle());

            $document = new Document(
                $indexable->getId(),
                $indexable->getData()
            );

            $response = $elasticaType->addDocument($document);
        }

        return true;
    }

    public function reset($index)
    {
        if ($this->hasIndex($index)) {
            $this->client->getIndex($index)->delete();
        }
    }

    /**
     * Updates the mappings for the model.
     * @param  Elastica\Index $index
     * @return void
     */
    public function updateMappings($index)
    {
        $elasticaType = $index->getType($this->type->getHandle());

        $mapping = new Mapping();
        $mapping->setType($elasticaType);

        $mapping->setProperties($this->type->getMapping());
        $mapping->send();
    }

    /**
     * Create an index based on the model.
     * @return void
     */
    public function createIndex($name)
    {
        $index = $this->client->getIndex($name);
        $index->create([
            'analysis' => [
                'analyzer' => [
                    'trigram' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['standard', 'shingle'],
                    ],
                    'candy' => [
                        'tokenizer' => 'standard',
                        'filter' => ['standard', 'lowercase', 'stop', 'porter_stem'],
                    ],
                ],
                'filter' => [
                    'shingle' => [
                        'type' => 'shingle',
                        'min_shingle_size' => 2,
                        'max_shingle_size' => 3,
                    ],
                ],
            ],
        ]);
        $this->updateMappings($index);
        return $index;
    }
}
