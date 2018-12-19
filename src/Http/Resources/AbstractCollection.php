<?php

namespace GetCandy\Api\Http\Resources;

use Illuminate\Http\Resources\MissingValue;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class AbstractCollection extends ResourceCollection
{
    protected $only = [];

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $only = [])
    {
        parent::__construct($resource);
        $this->only = $only;
        $this->resource = $this->collectResource($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }

    // /**
    //  * Transform the resource into a JSON array.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return array
    //  */
    // public function toArray($request)
    // {
    //     return $this->collection->map->toArray($request)->all();
    // }

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
        if ($collects && ! $resource->first() instanceof $collects) {
            $collection = collect();
            $resource->each(function ($item) use ($collection, $collects) {
                $collection->push(
                    (new $collects($item))->only($this->only)
                );
            });
            $this->collection = $collection;
        } else {
            $collection = collect();
            $resource->each(function ($item) use ($collection, $collects) {
                $collection->push(
                    (new $collects($item))->only($this->only)
                );
            });
            $this->collection = $collection;
        }

        return $resource instanceof AbstractPaginator
                    ? $resource->setCollection($this->collection)
                    : $this->collection;
    }
}
