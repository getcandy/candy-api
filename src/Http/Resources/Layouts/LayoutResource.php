<?php

namespace GetCandy\Api\Http\Resources\Layouts;

use GetCandy\Api\Http\Resources\AbstractResource;

class LayoutResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'name' => $this->name,
            'handle' => $this->handle,
            'type' => $this->type,
        ];
    }

    public function includes()
    {
        return [];
        //     return [
    //         'children' => new CategoryCollection($this->whenLoaded('children'), $this->only),
    //         // 'routes' => new RouteCollection($this->whenLoaded('routes')),
    //     ];
    }
}
