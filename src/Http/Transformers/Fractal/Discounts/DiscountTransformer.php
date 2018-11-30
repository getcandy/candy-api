<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Discounts;

use Carbon\Carbon;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Traits\IncludesAttributes;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Channels\ChannelTransformer;

class DiscountTransformer extends BaseTransformer
{
    use IncludesAttributes;

    protected $availableIncludes = [
        'sets', 'attribute_groups', 'channels', 'rewards',
    ];

    public function transform(Discount $discount)
    {
        return [
            'id' => $discount->encodedId(),
            'attribute_data' => $discount->attribute_data,
            'start_at' => $discount->start_at ? Carbon::parse($discount->start_at)->toIso8601String() : null,
            'end_at' => $discount->end_at ? Carbon::parse($discount->end_at)->toIso8601String() : null,
            'priority' => $discount->priority,
            'status' => $discount->status,
            'stop_rules' => (bool) $discount->stop_rules,
            'uses' => $discount->uses,
        ];
    }

    /**
     * Include the sets in the resource.
     *
     * @param Discount $discount
     *
     * @return void
     */
    public function includeSets(Discount $discount)
    {
        return $this->collection($discount->sets, new DiscountSetTransformer);
    }

    /**
     * @param Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeChannels(Discount $discount)
    {
        $channels = app('api')->channels()->getChannelsWithAvailability($discount, 'discounts');

        return $this->collection($channels, new ChannelTransformer);
    }

    public function includeRewards(Discount $discount)
    {
        return $this->collection($discount->rewards, new DiscountRewardTransformer);
    }
}
