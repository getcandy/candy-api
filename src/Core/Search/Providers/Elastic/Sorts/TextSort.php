<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Sorts;

class TextSort extends AbstractSort
{
    public function getMapping()
    {
        return [
            $this->field.'.'.$this->handle => $this->dir,
        ];
    }
}
