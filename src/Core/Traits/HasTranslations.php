<?php

namespace GetCandy\Api\Core\Traits;

trait HasTranslations
{
    /**
     * Sets the name attribute to a json string.
     * @param array $value
     */
    public function setNameAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['name'] = json_encode($value);
        } else {
            $this->attributes['name'] = $value;
        }
    }

    public function getNameAttribute($value)
    {
        return json_decode($value, true);
    }

    public function translation($val, $locale = 'en')
    {
        if (is_null($this->name[$locale])) {
            return $this->name['en'];
        } elseif ($this->name[$locale] == '') {
            return;
        }

        return $this->name[$locale];
    }
}
