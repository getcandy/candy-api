<?php

namespace GetCandy\Api\Http\Resources\Currencies;

use Carbon\Carbon;
use GetCandy\Api\Http\Resources\AbstractResource;

class CurrencyResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'code' => $this->code,
            'enabled' => (bool) $this->enabled,
            'format' => $this->format,
            'decimal_point' => $this->decimal_point,
            'thousand_point' => $this->thousand_point,
            'default' => (bool) $this->default
        ];
    }
}
