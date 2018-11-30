<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Attributes;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class AttributeGroupTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'attributes',
    ];

    public function transform(AttributeGroup $group)
    {
        return [
            'id' => $group->encodedId(),
            'name' => $group->name,
            'handle' => $group->handle,
            'position' => (string) $group->position,
        ];
    }

    public function includeAttributes(AttributeGroup $group)
    {
        return $this->collection($group->attributes, new AttributeTransformer);
    }
}
