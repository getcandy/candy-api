<?php

namespace GetCandy\Api\Core\ReusablePayments\Resources;

use GetCandy\Api\Http\Resources\AbstractCollection;

class ReusablePaymentCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ReusablePaymentResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}
