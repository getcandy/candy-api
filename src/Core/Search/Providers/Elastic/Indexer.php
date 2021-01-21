<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Carbon\Carbon;
use Elastica\Client;
use Elastica\Document;
use Elastica\Reindex;
use Elastica\Type\Mapping;
use GetCandy\Api\Core\Languages\Actions\FetchLanguages;
use GetCandy\Api\Core\Scopes\ChannelScope;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use Illuminate\Database\Eloquent\Model;

class Indexer
{
    // use InteractsWithIndex;

    /**
     * @var int
     */
    protected $batch = 0;

    /**
     * The indice resolver.
     *
     * @var \GetCandy\Api\Core\Search\Providers\Elastic\IndiceResolver
     */
    protected $resolver;

    public function __construct(IndiceResolver $resolver)
    {
        $this->client = new Client(config('getcandy.search.client_config.elastic', []));
        $this->resolver = $resolver;
    }

    /**
     * Reindex a model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function reindex($model, $batchSize = 1000)
    {
        $type = $this->resolver->getType($model);

        $this->batch = 0;

        $languages = FetchLanguages::run([
            'paginate' => false,
        ]);

        $index = $this->getIndexName($type);

        $suffix = microtime(true);

        $model = new $model;

        $aliases = [];

        foreach ($languages as $language) {
            $alias = $index.'_'.$language->lang;
            $newIndex = $alias."_{$suffix}";
            $this->createIndex($alias."_{$suffix}", $type);
            $aliases[$alias] = $alias."_{$suffix}";
        }

        $models = $model->withoutGlobalScopes([
            CustomerGroupScope::class,
            ChannelScope::class,
        ])->limit($batchSize)
            ->offset($this->batch)
            ->get();

        $type->setSuffix($suffix);

        $aliasMapping = [];

        $indices = $this->client->getStatus()->getIndexNames();

        while ($models->count()) {
            $indexes = [];
            foreach ($models as $model) {
                $indexables = $type->getIndexDocument($model);
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
                $elasticaType = $index->getType($type->getHandle());
                $elasticaType->addDocuments($documents);
            }

            $elasticaType->addDocuments($documents);
            $elasticaType->getIndex()->refresh();

            echo ':batch:'.$this->batch;
            $this->batch += $batchSize;
            $models = $model->withoutGlobalScopes([
                CustomerGroupScope::class,
                ChannelScope::class,
            ])->limit($batchSize)->offset($this->batch)->get();
        }

        foreach ($aliases as $alias => $index) {
            $index = $this->client->getIndex($index);
            $index->addAlias($alias);

            $indices = $this->client->getStatus()->getIndicesWithAlias($alias);

            $currentTime = $this->getIndiceTime($index->getName());

            foreach ($indices as $name => $indice) {
                $fragments = explode('_', $indice->getName());
                $time = $this->getIndiceTime($indice->getName());

                if (! $time) {
                    $indice->delete();
                    continue;
                }

                if ($currentTime->gt($time)) {
                    $indice->delete();
                }
            }
        }
    }

    protected function getIndiceTime($name)
    {
        $fragments = explode('_', $name);
        try {
            return Carbon::createFromTimestampMs(end($fragments));
        } catch (\ErrorException $e) {
        }
    }

    /**
     * Updates the mappings for the model.
     *
     * @param  \Elastica\Index  $index
     * @param  mixed  $type
     * @return void
     */
    public function updateMappings($index, $type)
    {
        $elasticaType = $index->getType($type->getHandle());

        $mapping = new Mapping();
        $mapping->setType($elasticaType);

        $mapping->setProperties($type->getMapping());
        $mapping->send();
    }

    /**
     * Gets a timestamped index.
     *
     * @param  mixed  $type
     * @return string
     */
    protected function getIndexName($type)
    {
        return config('getcandy.search.index_prefix', 'candy').
            '_'.
            $type->getHandle();
    }

    /**
     * Index a single object.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    public function indexObject(Model $model)
    {
        $type = $this->resolver->getType($model);

        // Get our aliases
        $status = $this->client->getStatus();

        $index = $this->getIndexName($type);

        $langs = FetchLanguages::run([
            'paginate' => false,
        ]);

        $indexables = $type->getIndexDocument($model);

        foreach ($langs as $lang) {
            $alias = $index.'_'.$lang->lang;

            $indices = $status->getIndicesWithAlias($alias);

            $documents = $indexables->filter(function ($doc) use ($alias) {
                return $doc->getIndex() == $alias;
            });

            foreach ($indices as $indice) {
                $elasticaType = $indice->getType($type->getHandle());

                foreach ($indexables as $indexable) {
                    $document = new Document(
                        $indexable->getId(),
                        $indexable->getData()
                    );
                    $elasticaType->addDocument($document);
                }
            }
        }

        return true;
    }

    public function indexObjects($models)
    {
        $status = $this->client->getStatus();
        $langs = FetchLanguages::run([
            'paginate' => false,
        ]);

        $pending = [];

        foreach ($models as $model) {
            $type = $this->resolver->getType($model);
            $index = $this->getIndexName($type);

            $indexables = $type->getIndexDocument($model);

            foreach ($langs as $lang) {
                $alias = $index.'_'.$lang->lang;

                $indices = $status->getIndicesWithAlias($alias);

                $documents = $indexables->filter(function ($doc) use ($alias) {
                    return $doc->getIndex() == $alias;
                });

                foreach ($indices as $indice) {
                    $elasticaType = $indice->getType($type->getHandle());
                    $realIndex = $indice->getName();
                    if (empty($pending[$realIndex])) {
                        $pending[$realIndex]['docs'] = collect();
                        $pending[$realIndex]['type'] = $type;
                    }
                    foreach ($indexables as $indexable) {
                        $pending[$indice->getName()]['docs']->push(new Document(
                            $indexable->getId(),
                            $indexable->getData()
                        ));
                    }
                }
            }
        }

        foreach ($pending as $indexName => $data) {
            $index = $this->client->getIndex($indexName);
            $type = $index->getType($data['type']->getHandle());
            $type->addDocuments($data['docs']->toArray());
            $index->refresh();
        }
    }

    public function updateDocuments($models, $field = null)
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
     * NEW METHODS ABOVE
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------
     * ---------------------------------------------------------------------------------------------------.
     */
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

    /**
     * Cleans up the indexes for next time.
     *
     * @param  string  $suffix
     * @param  array  $aliases
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
     * Add a single model to the elastic index.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string|null  $suffix
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
     *
     * @return mixed
     */
    public function createIndex($name, $type)
    {
        $index = $this->client->getIndex($name);
        $index->create([
            'settings' => [
                'analysis' => [
                    'analyzer' => [
                        'trigram' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => ['shingle'],
                        ],
                        'standard_lowercase' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => ['lowercase'],
                        ],
                        'candy' => [
                            'tokenizer' => 'standard',
                            'filter' => ['lowercase', 'stop', 'porter_stem'],
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
            ],
        ]);
        $this->updateMappings($index, $type);

        return $index;
    }
}
