<?php

namespace GetCandy\Api\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class AbstractCollection extends ResourceCollection
{
    /**
     * Transform the resource into a JSON array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map->toArray($request)->all();
    }

    /**
     * Map the given collection resource into its individual resources.
     *
     * @param  mixed  $resource
     * @return mixed
     */
    protected function collectResource($resource)
    {
        if ($resource instanceof MissingValue) {
            return $resource;
        }

        $collects = $this->collects();


        $this->collection = $collects && ! $resource->first() instanceof $collects
            ? $resource->mapInto($collects)
            : $resource->toBase();


        return $resource instanceof AbstractPaginator
                    ? $resource->setCollection($this->collection)
                    : $this->collection;
    }
}