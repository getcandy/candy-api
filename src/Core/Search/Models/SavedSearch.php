<?php

namespace GetCandy\Api\Core\Search\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class SavedSearch extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    /**
     * Sets the payload attribute.
     *
     * @param  array  $val
     * @return void
     */
    public function setPayloadAttribute($val)
    {
        $this->attributes['payload'] = json_encode($val);
    }

    /**
     * Mutator for payload attribute.
     *
     * @param  array  $val
     * @return void
     */
    public function getPayLoadAttribute($val)
    {
        return json_decode($val, true);
    }
}
