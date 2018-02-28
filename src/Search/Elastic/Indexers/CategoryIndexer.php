<?php

namespace GetCandy\Api\Search\Elastic\Indexers;

use Elastica\Document;
use GetCandy\Api\Categories\Models\Category;
use Illuminate\Database\Eloquent\Model;

class CategoryIndexer extends BaseIndexer
{
    /**
     * @var Product
     */
    protected $model = Category::class;

    /**
     * @var string
     */
    public $type = 'categories';

    /**
     * Returns the Index document ready to be added
     * @param  Product $product
     * @return Document
     */
    public function getIndexDocument(Category $category)
    {
        return $this->getIndexables($category);
    }

    public function rankings()
    {
        return [
            "name^5", "name.english^3", "description^1"
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
            'name' => [
                'type' => 'text',
                'analyzer' => 'standard',
                'fields' => [
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

    protected function getCategories(Model $model, $lang = 'en')
    {
        return $model->children()->get()->map(function ($item) use ($lang) {
            return [
                'id' => $item->encodedId(),
                'name' => $item->attribute('name', null, $lang)
            ];
        })->toArray();
    }
}
