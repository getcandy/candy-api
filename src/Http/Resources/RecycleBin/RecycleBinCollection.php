<?php

namespace GetCandy\Api\Http\Resources\RecycleBin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RecycleBinCollection extends ResourceCollection
{
    public $collects = RecycleBinResource::class;

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'key' => 'value',
            ],
        ];
    }
}
