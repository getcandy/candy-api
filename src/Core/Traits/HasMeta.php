<?php

namespace GetCandy\Api\Core\Traits;

trait HasMeta
{
    /**
     * Mutator for setting meta column.
     *
     * @param  array|null  $val
     * @return string|null
     */
    public function setMetaAttribute(array $val = null)
    {
        $this->attributes['meta'] = $val ? json_encode($val) : null;
    }

    /**
     * Mutator for setting meta attribute.
     *
     * @param  string  $val
     * @return array
     */
    public function getMetaAttribute($val)
    {
        return json_decode($val ?? '[]', true);
    }
}
