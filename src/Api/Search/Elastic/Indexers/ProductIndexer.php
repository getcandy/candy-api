<?php

namespace GetCandy\Api\Search\Elastic\Indexers;

use GetCandy\Api\Products\Models\Product;
use Elastica\Document;

class ProductIndexer extends BaseIndexer
{
    /**
     * @var Product
     */
    protected $model = Product::class;

    /**
     * @var string
     */
    public $type = 'products';

    /**
     * Returns the Index document ready to be added
     * @param  Product $product
     * @return Document
     */
    public function getIndexDocument(Product $product)
    {
        return $this->getIndexables($product);
    }

    public function rankings()
    {
        return [
            "name^5",  "sku^4", "name.english^3", "description^1"
        ];
    }

    /**
     * Returns the mapping used by elastic search
     * @return array
     */
    public function mapping()
    {
        return [
            'id' => [
                'type' => 'text'
            ],
            'description' => [
                'type' => 'text',
                'analyzer' => 'standard',
            ],
            'sku' => [
                'type' => 'text',
                'analyzer' => 'standard'
            ],
            'created_at'  => [
                'type' => 'date'
            ],
            'departments' => [
                'type' => 'nested',
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                        'index' => true
                    ],
                    'name' => [
                        'type' => 'text'
                    ]
                ]
            ],
            'customer_groups' => [
                'type' => 'nested',
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                        'index' => true
                    ],
                    'name' => [
                        'type' => 'text'
                    ],
                    'handle' => [
                        'type' => 'keyword',
                        'index' => true
                    ]
                ]
            ],
            'channels' => [
                'type' => 'nested',
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                        'index' => true
                    ],
                    'name' => [
                        'type' => 'text'
                    ],
                    'handle' => [
                        'type' => 'keyword',
                        'index' => true
                    ]
                ]
            ],
            'thumbnail' => [
                'type' => 'text'
            ],
            'min_price' => [
                "type" => "scaled_float",
                "scaling_factor" => 100
            ],
            'max_price' => [
                "type" => "scaled_float",
                "scaling_factor" => 100
            ],
            'name' => [
                'type' => 'text',
                'analyzer' => 'standard',
                'fields' => [
                    'sortable' => [
                        'type' => 'keyword'
                    ],
                    'en' => [
                        'type' => 'text',
                        'analyzer' => 'english'
                    ],
                    'trigram' => [
                        'type' => 'text',
                        'analyzer' => 'trigram'
                    ]
                ]
            ]
        ];
    }
}
