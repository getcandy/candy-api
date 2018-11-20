<?php

namespace GetCandy\Api\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use GetCandy\Api\Core\Channels\Services\ChannelService;
use GetCandy\Api\Core\Languages\Services\LanguageService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\MissingValue;

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
     * The resource data array
     *
     * @var array
     */
    protected $data = [];


    /**
     * Set the singular item
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
     * Set the only fields we want to return
     *
     * @param array $fields
     * @return self
     */
    public function only($fields = [])
    {
        if ($fields instanceof Collection) {
           $fields = $fields->toArray();
        }
        $this->only = collect($fields);
        return $this;
    }

    /**
     * Set the channel we want to use
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
     * Set the language to use
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
    public function __construct($resource)
    {
        $this->resource = $resource;
        $this->only = collect();
    }

    public function toArray($request)
    {
        $attributes = array_merge($this->payload(), $this->map($this->attribute_data));
        return array_merge($attributes, $this->includes());
    }

    protected function relationLoaded($relation)
    {
        if ($this->whenLoaded($relation) == MissingValue::class) {
            return false;
        }
        return true;
    }

    /**
     * Map the attributes
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
            if ($this->only->count() && !$this->only->contains($field)) {
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