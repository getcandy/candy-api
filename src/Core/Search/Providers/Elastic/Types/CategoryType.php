<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Types;

use Elastica\Document;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Scopes\ChannelScope;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use Illuminate\Database\Eloquent\Model;

class CategoryType extends BaseType
{
    /**
     * @var string
     */
    protected $model = Category::class;

    /**
     * @var string
     */
    public $handle = 'categories';

    protected $mapping = [
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
    ];

    /**
     * Returns the Index document ready to be added.
     *
     * @param  \GetCandy\Api\Core\Categories\Models\Category  $category
     * @return mixed
     */
    public function getIndexDocument(Category $category)
    {
        return $this->getIndexables($category);
    }

    public function rankings()
    {
        return config('getcandy.search.ranking.categories');
    }

    protected function getCategories(Model $model, $lang = 'en')
    {
        return $model->children()->withoutGlobalScopes([
            CustomerGroupScope::class,
            ChannelScope::class,
        ])->get()->map(function ($item) use ($lang) {
            return [
                'id' => $item->encodedId(),
                'name' => $item->attribute('name', null, $lang),
            ];
        });
    }
}
