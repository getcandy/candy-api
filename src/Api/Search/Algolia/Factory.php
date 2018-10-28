<?php

namespace GetCandy\Api\Search\Algolia;

class Factory
{
    protected $indexables = [];

    public function against($indexable)
    {
        if (empty($indexables[$indexable])) {
            return $this->create($indexable);
        }
        return $this->indexables[$indexable];
    }

    public function create($id)
    {
        return new Indexable($id);
    }
}
