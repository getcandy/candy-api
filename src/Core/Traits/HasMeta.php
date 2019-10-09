<?php

namespace GetCandy\Api\Core\Traits;

trait HasMeta
{
    /**
     * Mutator for setting meta column.
     *
     * @param array $val
     * @return void
     */
    public function setMetaAttribute(array $val = null)
    {
        $this->attributes['meta'] = json_encode($val);
    }

    /**
     * Mutator for setting meta attribute.
     *
     * @param string $val
     * @return array
     */
    public function getMetaAttribute($val)
    {
        return json_decode($val ?? '[]', true);
    }
}
