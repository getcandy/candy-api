<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Tags;

use GetCandy\Api\Core\Tags\Models\Tag;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class TagTransformer extends BaseTransformer
{
    protected $availableIncludes = [];

    /**
     * Decorates the tag object for viewing.
     * @param  Tag $product
     * @return array
     */
    public function transform(Tag $tag)
    {
        return [
            'id' => $tag->encodedId(),
            'name' => $tag->name,
        ];
    }
}
