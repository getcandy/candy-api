<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Sorts;

class NestedSort extends AbstractSort
{
    public function getMapping()
    {
        $column = $this->handle;

        if ($this->handle == 'min_price' || $this->handle == 'max_price') {
            $this->handle = 'pricing';
            $column = $this->handle.'.'.str_replace('_price', '', $column);
        }

        $sort = [
            $column => [
                'order' => $this->dir,
                'mode' => $this->mode,
                'nested_path' => $this->field,
                'nested_filter' => [
                    'bool' => [
                        'must' => [],
                    ],
                ],
            ],
        ];

        foreach ($this->customerGroups() as $group) {
            $sort[$column]['nested_filter']['bool']['must'] = [
                'match' => [
                    $this->handle.'.id' => $group->encodedId(),
                ],
            ];
        }

        return $sort;
    }
}
