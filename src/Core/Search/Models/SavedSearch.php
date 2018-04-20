<?php

namespace GetCandy\Api\Core\Search\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class SavedSearch extends BaseModel
{
    protected $hashids = 'main';

    /**
     * Sets the payload attribute.
     *
     * @param json $val
     *
     * @return void
     */
    public function setPayloadAttribute($val)
    {
        $this->attributes['payload'] = json_encode($val);
    }

    /**
     * Mutator for payload attribute.
     *
     * @param string $val
     *
     * @return void
     */
    public function getPayLoadAttribute($val)
    {
        return json_decode($val, true);
    }
}
