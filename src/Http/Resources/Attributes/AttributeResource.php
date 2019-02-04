<?php

namespace GetCandy\Api\Http\Resources\Attributes;

use GetCandy\Api\Http\Resources\AbstractResource;

class AttributeResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'handle' => $this->handle,
            'position' => (string) $this->position,
            'filterable' => (bool) $this->filterable,
            'scopeable' => (bool) $this->scopeable,
            'translatable' => (bool) $this->translatable,
            'variant' => (bool) $this->variant,
            'searchable' => (bool) $this->searchable,
            'localised' => (bool) $this->translatable,
            'type' => $this->type,
            'required' => (bool) $this->required,
            'lookups' => $this->lookups,
            'system' => (bool) $this->system,
        ];
    }

    public function includes()
    {
        return [
            'group' => ['data' => new AttributeGroupResource($this->whenLoaded('group'))],
        ];
    }
}
