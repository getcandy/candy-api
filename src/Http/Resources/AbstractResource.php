<?php

namespace GetCandy\Api\Http\Resources;

use Illuminate\Support\Collection;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

abstract class AbstractResource extends JsonResource
{
    protected $only = [];

    /**
     * @var string
     */
    protected $channel = null;

    /**
     * @var string
     */
    protected $language = null;

    /**
     * The resource data array.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Set the singular item.
     *
     * @param mixed $resource
     * @return self
     */
    public function item($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Set the only fields we want to return.
     *
     * @param array $fields
     * @return self
     */
    public function only($fields = [])
    {
        if ($fields instanceof Collection) {
            $fields = $fields->toArray();
        } elseif (is_string($fields)) {
            $fields = explode(',', $fields);
        }
        $this->only = collect($fields);

        return $this;
    }

    /**
     * Set the channel we want to use.
     *
     * @param string $channel
     * @return self
     */
    public function channel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Set the language to use.
     *
     * @param string $language
     * @return self
     */
    public function language($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $only = [])
    {
        $this->resource = $resource;
        $this->only = collect($only);
    }

    public function toArray($request)
    {
        $attributes = array_merge($this->payload(), $this->custom_attributes ?? []);
        if ($request->full_response) {
            $attributes = array_merge($attributes, [
                'attribute_data' => $this->attribute_data,
            ]);
            foreach ($this->optional() as $key => $value) {
                $attributes[$key] = $value;
            }
        } else {
            $attributes = array_merge($attributes, $this->map($this->attribute_data ?? []));
            foreach ($this->optional() as $key => $value) {
                $attributes[$key] = $this->when($request->{$key}, $value);
            }
        }

        return array_merge($attributes, $this->includes());
    }

    protected function relationLoaded($relation)
    {
        if ($this->whenLoaded($relation) instanceof MissingValue) {
            return false;
        }

        return true;
    }

    protected function include($relation, $resource)
    {
        return $this->when($this->relationLoaded($relation), function () use ($relation, $resource) {
            return ['data' => new $resource($this->whenLoaded($relation))];
        });
    }

    /**
     * The included resources.
     *
     * @return array
     */
    public function includes()
    {
        return [];
    }

    /**
     * Define optional attributes.
     *
     * @return array
     */
    public function optional()
    {
        return [];
    }

    /**
     * Map the attributes.
     *
     * @param array $data
     * @return void
     */
    protected function map($data)
    {
        if (empty($data)) {
            return [];
        }
        $modified = [];
        foreach ($data as $field => $value) {
            if ($this->only->count() && ! $this->only->contains($field)) {
                continue;
            }
            $modified[$field] = $this->resource->attribute(
                $field,
                $this->channel,
                $this->language
            );
        }

        return $modified;
    }

    /**
     * Create new anonymous resource collection.
     *
     * @param  mixed  $resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function respondWithCollection()
    {
        return new AnonymousResourceCollection($this->resource, get_called_class());
    }
}
