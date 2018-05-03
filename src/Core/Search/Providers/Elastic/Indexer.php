<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Client;
use Elastica\Status;
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

    protected function getBaseIndexName()
    {
        return config('getcandy.search.index_prefix') . '_' . $this->type->getHandle();
    }

    public function indexAll($model)
    {
        $languages = app('api')->languages()->all();

        $defaultLang = $languages->first(function ($lang) {
            return $lang->default;
        });

        $this->type = $this->getType($model);

        $indexName = $this->getBaseIndexName() . "_{$defaultLang->lang}";

        $model = new $model;

        $suffix = $this->getIndexSuffix($indexName);

        $aliases = [];

        // Go through our languages and create a new index at the correct version
        foreach ($languages as $language) {
            $alias = $this->getBaseIndexName() . '_' . $language->lang;
            $this->createIndex(
                $alias . "_{$suffix}"
            );
            $aliases[] = $alias;
        }

        foreach ($model->withoutGlobalScopes()->take(1)->get() as $model) {
            $this->indexObject($model, $suffix);
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

    protected function getIndexSuffix($name)
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


    protected function indexObject(Model $model, $suffix)
    {
        // $this->against($model);
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

    // public function indexAll($model)
    // {
    //     $this->against($model);

    //     // $model = new $model;

    //     // foreach ($model->withoutGlobalScopes()->get() as $model) {
    //     //     $this->indexObject($model, true);
    //     // }
    // }

    // public function updateDocument($model, $field)
    // {
    //     $this->against($model);
    //     $index = $this->getIndex(
    //         $this->indexer->getIndexName()
    //     );
    //     $this->indexer->getUpdatedDocument($model, $field, $index);
    //     $elasticaType = $index->getType($this->indexer->type);
    //     $elasticaType->addDocument($document);
    // }

    // public function updateDocuments($models, $field)
    // {
    //     $this->against($models->first());
    //     $index = $this->getIndex(
    //         $this->indexer->getIndexName()
    //     );
    //     $documents = $this->indexer->getUpdatedDocuments($models, $field, $index);
    //     $index->addDocuments($documents);
    // }

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
