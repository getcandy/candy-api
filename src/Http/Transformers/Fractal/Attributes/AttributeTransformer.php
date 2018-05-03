<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Attributes;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class AttributeTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'group',
    ];

    /**
     * Decorates the attribute object for viewing.
     * @param  Attribute $product
     * @return array
     */
    public function transform(Attribute $attribute)
    {
        return [
            'id' => $attribute->encodedId(),
            'name' => $attribute->name,
            'handle' => $attribute->handle,
            'position' => (string) $attribute->position,
            'filterable' => (bool) $attribute->filterable,
            'scopeable' => (bool) $attribute->scopeable,
            'translatable' => (bool) $attribute->translatable,
            'variant' => (bool) $attribute->variant,
            'searchable' => (bool) $attribute->searchable,
            'localised' => (bool) $attribute->translatable,
            'type' => $attribute->type,
            'required' => (bool) $attribute->required,
            'lookups' => $attribute->lookups,
            'system' => (bool) $attribute->system,
        ];
    }

    public function includeGroup(Attribute $attribute)
    {
        return $this->item($attribute->group, new AttributeGroupTransformer);
    }
}
