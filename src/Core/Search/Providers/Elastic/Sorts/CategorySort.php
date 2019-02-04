<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Sorts;

use GetCandy\Api\Core\Categories\Models\Category;

class CategorySort extends AbstractSort
{
    /**
     * The category model.
     *
     * @var Category
     */
    protected $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get the sort mapping.
     *
     * @return array
     */
    public function getMapping()
    {
        if ($this->category->sort == 'custom') {
            $sort = [
                'departments.position' => [
                    'order' => 'asc',
                    'mode' => 'max',
                    'nested_path' => 'departments',
                    'nested_filter' => [
                        'bool' => [
                            'must' => [
                                'match' => [
                                    'departments.id' => $this->category->encodedId(),
                                ],
                            ],
                        ],
                    ],
                ],
            ];
            $defaultSort[] = $sort;
        } else {
            // sort
            $sort = explode(':', $this->category->sort);
            if ($sort[0] == 'sku') {
                $sort[0] = 'sku.sort';
            }
            $defaultSort = [$sort[0] => $sort[1] ?? 'asc'];
        }

        return $defaultSort;
    }
}
