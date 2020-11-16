<?php

namespace GetCandy\Api\Core\Search\Indexables;

class ProductIndexable extends AbstractIndexable
{
    public function getMapping()
    {
        return array_merge(parent::getMapping(), [
            'id' => [
                'type' => 'text',
            ],
            'description' => [
                'type' => 'text',
                'analyzer' => 'standard',
            ],
            'popularity' => [
                'type' => 'integer',
            ],
            'sku' => [
                'type' => 'text',
                'analyzer' => 'standard',
                'fields' => [
                    'sortable' => [
                        'type' => 'keyword',
                    ],
                    'suggest' => [
                        'type' => 'completion',
                    ],
                    'lowercase' => [
                        'type' => 'text',
                        'analyzer' => 'standard_lowercase',
                    ],
                ],
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
                'fields' => [
                    'sortable' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'max_price' => [
                'type' => 'scaled_float',
                'scaling_factor' => 100,
                'fields' => [
                    'sortable' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'breadcrumbs' => [
                'type' => 'text',
                'analyzer' => 'standard',
                'fields' => [
                    'en' => [
                        'type' => 'text',
                        'analyzer' => 'english',
                    ],
                ],
            ],
            'name' => [
                'type' => 'text',
                'analyzer' => 'standard',
                'fields' => [
                    'sortable' => [
                        'type' => 'keyword',
                    ],
                    'suggest' => [
                        'type' => 'completion',
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
        ]);
    }
}
