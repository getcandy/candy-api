<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Sorts;

use GetCandy\Api\Core\Categories\Models\Category;

class CategorySort extends AbstractSort
{
    /**
     * The category model.
     *
     * @var \GetCandy\Api\Core\Categories\Models\Category
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
            $defaultSort = $sort;
        } else {
            $defaultSort = [];

            // sort
            $sortable = explode('|', $this->category->sort);
            foreach ($sortable as $sort) {
                $segments = explode(':', $sort);
                $defaultSort[$segments[0].'.sortable'] = $segments[1] ?? 'asc';
            }
        }

        return $defaultSort;
    }
}
