<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;

trait HasAttributes
{
    /**
     * Get all of the tags for the post.
     */
    public function attributes()
    {
        return $this->morphToMany(Attribute::class, 'attributable')->orderBy('position', 'asc');
    }

    public function hasAttribute($attribute)
    {
        return isset($this->attribute_data[$attribute]);
    }

    public function attributeGroup()
    {
        return $this->hasOne(AttributeGroup::class)->withTimestamps();
    }

    public function attribute($handle, $channel = null, $locale = null)
    {
        if (! $locale) {
            $locale = app()->getLocale();
        }

        if (! $channel) {
            $factory = app()->getInstance()->make(ChannelFactoryInterface::class);
            $channel = $factory->getChannel()->handle;
        }

        if (! empty($this->attribute_data[$handle][$channel]) &&
            is_array($this->attribute_data[$handle][$channel]) &&
            array_key_exists($locale, $this->attribute_data[$handle][$channel])) {
            return $this->attribute_data[$handle][$channel][$locale];
        }

        if (empty($this->attribute_data[$handle][$channel])) {
            $defaultChannel = app('api')->channels()->getDefaultRecord();
            $channel = $defaultChannel->handle;
        }

        $userLocale = app()->getLocale();
        if (! empty($this->attribute_data[$handle][$channel][$userLocale])) {
            return $this->attribute_data[$handle][$channel][$userLocale];
        } elseif (empty($this->attribute_data[$handle][$channel][$userLocale])) {
            return;
        } elseif (is_null($this->attribute_data[$handle][$channel][$userLocale])) {
            $channel = 'webstore';
            $locale = $locale->lang;
        }
    }

    public function getNameAttribute()
    {
        return $this->attribute('name');
    }

    public function getAttributeDataAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setAttributeDataAttribute($val)
    {
        if (! $this->id) {
            $this->attributes['attribute_data'] = json_encode($this->mapAttributes($val));
        } else {
            // dd(json_encode($val));
            $this->attributes['attribute_data'] = json_encode($val);
        }
    }

    /**
     * Prepares the attribute data for saving to the database.
     * @param  array  $data
     * @return array
     */
    public function parseAttributeData(array $data)
    {
        $valueMapping = [];
        $structure = $this->getDataMapping();

        foreach ($data as $attribute) {
            // Do this so we can reset the structure without hitting DB again
            $newData[$attribute['key']] = $structure;

            // Set Attribute
            $valueMapping[$attribute['key']][$attribute['channel']][$attribute['locale']] = $attribute['value'];

            // Map the rest of the attribute data
            foreach ($valueMapping as $attribute => $value) {
                foreach ($value as $map => $value) {
                    array_set($newData[$attribute], $map, $value);
                }
            }
        }

        return $newData;
    }

    /**
     * Gets the current attribute data mapping.
     * @return array
     */
    public function getDataMapping()
    {
        $structure = [];
        $languagesArray = [];
        // Get our languages
        $languages = app('api')->languages()->getDataList();
        foreach ($languages as $lang) {
            $languagesArray[$lang->lang] = null;
        }
        // Get our channels
        $channels = app('api')->channels()->getDataList();
        foreach ($channels as $channel) {
            $structure[$channel->handle] = $languagesArray;
        }

        return $structure;
    }

    protected function mapAttributes($data)
    {
        $mapping = $this->getDataMapping();

        $attributes = app('api')->attributes()->getHandles();
        $attributeData = [];
        $assigned = [];
        foreach ($attributes as $attribute) {
            if (! empty($data[$attribute['handle']])) {
                foreach ($mapping as $key => $map) {
                    foreach ($data[$attribute['handle']] as $locale => $value) {
                        $mapping[$key][$locale] = $value;
                    }
                }
                $assigned[] = $attribute['id'];
                $attributeData[$attribute['handle']] = $mapping;
            }
        }

        return $attributeData;
    }
}
