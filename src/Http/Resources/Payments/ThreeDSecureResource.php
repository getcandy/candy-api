<?php

namespace GetCandy\Api\Http\Resources\Payments;

use GetCandy\Api\Http\Resources\AbstractResource;

class ThreeDSecureResource extends AbstractResource
{
    public function payload()
    {
        return $this->params();
    }
}
