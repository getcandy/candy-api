<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Sorts;

class NestedSort extends AbstractSort
{
    public function getMapping()
    {
        return [
            $this->handle => [
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
    }
}
