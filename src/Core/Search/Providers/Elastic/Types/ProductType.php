<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Types;

use Elastica\Document;
use GetCandy\Api\Core\Products\Models\Product;

class ProductType extends BaseType
{
    /**
     * @var Product
     */
    protected $model = Product::class;

    /**
     * @var string
     */
    protected $handle = 'products';

    /**
     * @var array
     */
    protected $mapping = [
        'id' => [
            'type' => 'text',
        ],
        'description' => [
            'type' => 'text',
            'analyzer' => 'standard',
        ],
        'sku' => [
            'type' => 'text',
            'analyzer' => 'standard',
        ],
        'created_at'  => [
            'type' => 'date',
        ],
        'departments' => [
            'type' => 'nested',
            'properties' => [
                'id' => [
                    'type' => 'keyword',
                    'index' => true,
                ],
                'name' => [
                    'type' => 'text',
                ],
                'position' => [
                    'type' => 'integer',
                ],
            ],
        ],
        'customer_groups' => [
            'type' => 'nested',
            'properties' => [
                'id' => [
                    'type' => 'keyword',
                    'index' => true,
                ],
                'name' => [
                    'type' => 'text',
                ],
                'handle' => [
                    'type' => 'keyword',
                    'index' => true,
                ],
            ],
        ],
        'channels' => [
            'type' => 'nested',
            'properties' => [
                'id' => [
                    'type' => 'keyword',
                    'index' => true,
                ],
                'name' => [
                    'type' => 'text',
                ],
                'handle' => [
                    'type' => 'keyword',
                    'index' => true,
                ],
            ],
        ],
        'pricing' => [
            'type' => 'nested',
            'properties' => [
                'id' => [
                    'type' => 'keyword',
                    'index' => true,
                ],
                'name' => [
                    'type' => 'text',
                ],
                'max' => [
                    'type' => 'scaled_float',
                    'scaling_factor' => 100,
                ],
                'min' => [
                    'type' => 'scaled_float',
                    'scaling_factor' => 100,
                ],
            ],
        ],
        'min_price' => [
            'type' => 'scaled_float',
            'scaling_factor' => 100,
        ],
        'max_price' => [
            'type' => 'scaled_float',
            'scaling_factor' => 100,
        ],
        'name' => [
            'type' => 'text',
            'analyzer' => 'standard',
            'fields' => [
                'sortable' => [
                    'type' => 'keyword',
                ],
                'en' => [
                    'type' => 'text',
                    'analyzer' => 'english',
                ],
                'trigram' => [
                    'type' => 'text',
                    'analyzer' => 'trigram',
                ],
            ],
        ],
    ];


    /**
     * Returns the Index document ready to be added.
     * @param  Product $product
     * @return Document
     */
    public function getIndexDocument(Product $product)
    {
        return $this->getIndexables($product);
    }

    public function getUpdatedDocument($model, $field, $index)
    {
        $method = 'update'.camel_case($field);
        if (method_exists($this, $method)) {
            return $this->{$method}($model, $index);
        }
    }

    public function getUpdatedDocuments($models, $field, $index)
    {
        $method = 'update'.camel_case($field);
        $collection = [];
        if (method_exists($this, $method)) {
            foreach ($models as $model) {
                $collection[] = $this->{$method}($model, $index);
            }
        }

        return $collection;
    }

    protected function updateCategories($model, $index)
    {
        $type = $index->getType(
            $this->type
        );

        $document = $type->getDocument($model->encodedId());

        $categories = $this->getCategories($model);

        $document->set('departments', $categories);

        return $document;
    }

    public function getIndexDocuments($products)
    {
        $collection = collect();
        foreach ($products as $product) {
            $indexables = $this->getIndexDocument($product);
            foreach ($indexables as $document) {
                $collection->push($document);
            }
        }

        return $collection;
    }

    public function rankings()
    {
        return [
            'name^5',  'sku^4', 'name.english^3', 'description^1',
        ];
    }
}
