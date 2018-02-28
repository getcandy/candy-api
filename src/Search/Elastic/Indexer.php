<?php
namespace GetCandy\Api\Search\Elastic;

use GetCandy\Api\Search\IndexContract;
use Illuminate\Database\Eloquent\Model;
use Elastica\Document;
use Elastica\Type\Mapping;

class Indexer extends AbstractProvider implements IndexContract
{
    /**
     * @var mixed
     */
    protected $indexer;

    /**
     * @var array
     */
    protected $categories = [];

    /**
     * Adds a model to the index
     * @param  Model  $model
     * @return boolean
     */
    public function indexObject(Model $model)
    {
        // Get the indexer.
        // $indexer = $this->getIndexer($model);
        // $index =
        // $elasticaType = $index->getType($indexer->type);
        // $response = $elasticaType->addDocument($indexer->getIndexDocument($model));

        $this->against($model);

        $indexables = $this->indexer->getIndexDocument($model);

        foreach ($indexables as $indexable) {
            $index = $this->getIndex($indexable->getIndex());

            $elasticaType = $index->getType($this->indexer->type);

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
            $this->client()->getIndex($index)->delete();
        }
    }

    /**
     * Updates the mappings for the model
     * @param  Elastica\Index $index
     * @return void
     */
    public function updateMappings($index)
    {
        $elasticaType = $index->getType($this->indexer->type);

        $mapping = new Mapping();
        $mapping->setType($elasticaType);

        $mapping->setProperties($this->indexer->mapping());
        $mapping->send();
    }

    /**
     * Create an index based on the model
     * @return void
     */
    public function createIndex()
    {
        $index = $this->client()->getIndex('getcandy');
        $index->create();
    }

    /**
     * Returns the index for the model
     * @return Elastica\Index
     */
    public function getIndex($name = null)
    {
        $index = $this->client()->getIndex($name);

        if (!$this->hasIndex($name)) {
            $index->create([
                'analysis' => [
                    'analyzer' => [
                        'trigram' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => ['standard', 'shingle']
                        ],
                        'candy' => [
                            'tokenizer' => 'standard',
                            'filter' => ["standard", "lowercase", "stop", "porter_stem"]
                        ]
                    ],
                    'filter' => [
                        'shingle' => [
                            'type' => 'shingle',
                            'min_shingle_size' => 2,
                            'max_shingle_size' => 3
                        ]
                    ]
                ]
            ]);
            $index->addAlias($name . '_alias');
            // ...and update the mappings
            $this->updateMappings($index);
        }
        return $index;
    }
}
