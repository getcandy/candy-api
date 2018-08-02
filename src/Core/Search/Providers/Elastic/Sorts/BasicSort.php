<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Sorts;

class BasicSort extends AbstractSort
{
    public function getMapping()
    {
        return [
            $this->field => $this->dir,
        ];
    }
}
