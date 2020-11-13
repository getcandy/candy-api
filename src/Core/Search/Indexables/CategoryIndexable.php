<?php

namespace GetCandy\Api\Core\Search\Indexables;

class CategoryIndexable extends AbstractIndexable
{
    public function getMapping()
    {
        return array_merge(parent::getMapping(), [
            'id' => [
                'type' => 'text',
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
            'thumbnail' => [
                'type' => 'text',
            ],
            'name' => [
                'type' => 'text',
                'analyzer' => 'standard',
                'fields' => [
                    'en' => [
                        'type' => 'text',
                        'analyzer' => 'english',
                    ],
                    'sortable' => [
                        'type' => 'keyword',
                    ],
                    'suggest' => [
                        'type' => 'completion',
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
