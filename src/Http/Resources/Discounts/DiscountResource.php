<?php

namespace GetCandy\Api\Http\Resources\Discounts;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;

class DiscountResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'priority' => $this->priority,
            'status' => $this->status,
            'stop_rules' => (bool) $this->stop_rules,
            'uses' => $this->uses,
        ];
    }

    public function includes()
    {
        return [
            'items' => new DiscountItemCollection($this->whenLoaded('items')),
            'rewards' => new DiscountRewardCollection($this->whenLoaded('rewards')),
            'sets' => new DiscountSetCollection($this->whenLoaded('sets')),
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
        ];
    }
}
