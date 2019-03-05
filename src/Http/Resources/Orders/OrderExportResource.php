<?php

namespace GetCandy\Api\Http\Resources\Orders;

use GetCandy\Api\Http\Resources\AbstractResource;

class OrderExportResource extends AbstractResource
{
    public function payload()
    {
        return [
            'format' => $this->getFormat(),
            'content' => $this->getContent(),
        ];
    }
}
