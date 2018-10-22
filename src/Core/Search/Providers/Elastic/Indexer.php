<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Elastica\Client;
use Elastica\Reindex;
use Elastica\Document;
use Elastica\Type\Mapping;
use Illuminate\Database\Eloquent\Model;

class Indexer
{
    use InteractsWithIndex;

    protected $batch = 0;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Updates the mapping for a model.
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
            $indexBasename = $baseIndex."_{$language->lang}";

            $currentIndex = $this->client->getIndex($indexBasename."_{$currentSuffix}");

            $newIndex = $this->createIndex(
                $indexBasename."_{$nextSuffix}"
            );

            $reindexer = new Reindex($currentIndex, $newIndex);

            $reindexer->run();

            $aliases[] = $indexBasename;
        }

        $this->cleanup($nextSuffix, $aliases);

        return true;
    }

    public function updateDocument($model, $field)
    {
        $this->against($model);
        $index = $this->getIndex(
            $this->indexer->getIndexName()
        );
        $this->indexer->getUpdatedDocument($model, $field, $index);
        $elasticaType = $index->getType($this->indexer->type);
        $elasticaType->addDocument($document);
    }

    public function updateDocuments($models, $field)
    {
        $this->against($models->first());

        $type = $this->getType($models->first());
        $index = $this->getCurrentIndex();
        $documents = $type->getUpdatedDocuments($models, $field, $index);

        $docs = [];

        foreach ($documents as $document) {
            foreach ($document as $doc) {
                $docs[] = $document = new Document(
                    $doc->getId(),
                    $doc->getData(),
                    $type->getHandle()
                );
            }
        }

        $index->addDocuments($docs);
    }

    /**
     * Reindexes all indexes for a model.
     *
     * @param string $model
     * @return void
     */
    public function indexAll($model)
    {
        $this->type = $this->getType($model);

        $this->batch = 0;

        $languages = app('api')->languages()->all();

        $indexName = $this->getDefaultIndex();

        $model = new $model;

        $suffix = $this->getNextIndexSuffix($indexName);

        $aliases = [];

        // Go through our languages and create a new index at the correct version
        foreach ($languages as $language) {
            $alias = $this->getBaseIndexName().'_'.$language->lang;
            $this->createIndex(
                $alias."_{$suffix}"
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
                $index = $this->client->getIndex($key);
                $elasticaType = $index->getType($this->type->getHandle());
                $elasticaType->addDocuments($documents);
            }

            $elasticaType->addDocuments($documents);
            $elasticaType->getIndex()->refresh();

            echo ':batch:'.$this->batch;
            $this->batch += 1000;
            $models = $model->withoutGlobalScopes()->limit(1000)->offset($this->batch)->get();
        }

        $this->cleanup($suffix, $aliases);

        return true;
    }

    /**
     * Cleans up the indexes for next time.
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
            $index = $this->client->getIndex($alias."_{$suffix}");
            $index->addAlias($alias);
            $this->reset($alias."_{$remove}");
        }
    }

    /**
     * Index a single object.
     *
     * @param Model $model
     * @return void
     */
    public function indexObject(Model $model)
    {
        $this->type = $this->getType($model);
        $indexName = $this->getDefaultIndex();
        if (! $this->suffix) {
            $this->suffix = $this->getCurrentIndexSuffix($indexName);
        }

        return $this->addToIndex($model, $this->suffix);
    }

    /**
     * Add a single model to the elastic index.
     *
     * @param Model $model
     * @param string $suffix
     * @return bool
     */
    protected function addToIndex(Model $model, $suffix = null)
    {
        $type = $this->type->setSuffix($suffix);

        $indexables = $type->getIndexDocument($model);

        foreach ($indexables as $indexable) {
            $index = $this->client->getIndex(
                $indexable->getIndex()
            );

            $elasticaType = $index->getType($this->type->getHandle());

            $document = new Document(
                $indexable->getId(),
                $indexable->getData()
            );

            $elasticaType->addDocument($document);
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
