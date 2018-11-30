<?php

namespace GetCandy\Api\Core\Discounts\Services;

use Carbon\Carbon;
use GetCandy\Api\Core\Discounts\RewardSet;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Discounts\Discount as DiscountFactory;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;
use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;

class DiscountService extends BaseService
{
    public function __construct()
    {
        $this->model = new Discount();
    }

    /**
     * Create a discount.
     *
     * @param array $data
     *
     * @return Discount
     */
    public function create(array $data)
    {
        $discount = new Discount;
        $discount->attribute_data = $data;

        if (! empty($data['start_at'])) {
            $discount->start_at = Carbon::parse($data['start_at']);
        }
        if (isset($data['end_at'])) {
            $discount->end_at = Carbon::parse($data['end_at']);
        }

        if (! empty($data['uses'])) {
            $discount->uses = $data['uses'];
        }

        $discount->status = ! empty($data['status']);
        $discount->save();

        if (! empty($data['channels']['data'])) {
            $discount->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        }

        event(new AttributableSavedEvent($discount));

        return $discount;
    }

    /**
     * Update an existing discount.
     *
     * @param string $id
     * @param array $data
     *
     * @return Discount
     */
    public function update($id, array $data)
    {
        $discount = $this->getByHashedId($id);
        $discount->start_at = Carbon::parse($data['start_at']);

        $discount->end_at = Carbon::parse($data['end_at']);
        $discount->priority = $data['priority'];
        $discount->stop_rules = $data['stop_rules'];
        $discount->status = $data['status'];

        $discount->save();

        // event(new AttributableSavedEvent($discount));
        if (isset($data['rewards']['data'])) {
            $discount->rewards()->delete();
            $this->syncRewards($discount, $data['rewards']['data']);
        }

        if (! empty($data['channels']['data'])) {
            $discount->channels()->sync(
                $this->getChannelMapping($data['channels']['data'])
            );
        }

        if (! empty($data['sets']['data'])) {
            $this->syncSets($discount, $data['sets']['data']);
        }

        return $discount;
    }

    /**
     * Set up sets and rewards with a discount.
     *
     * @param Discount $discount
     * @param array $sets
     *
     * @return Discount
     */
    public function syncSets($discount, array $sets)
    {
        //print_r($sets);exit;
        $discount->sets()->delete();

        foreach ($sets as $set) {
            $groupModel = $discount->sets()->create([
                'scope' => $set['scope'],
                'outcome' => (bool) $set['outcome'],
            ]);
            if (! empty($set['items']['data'])) {
                $set['items'] = $set['items']['data'];
            }
            foreach ($set['items'] as $item) {
                $model = $groupModel->items()->create($item);
                if (! empty($item['eligibles'])) {
                    foreach ($item['eligibles'] as $eligible) {
                        $model->saveEligible($item['type'], $eligible);
                    }
                }
            }
        }

        return $discount;
    }

    /**
     * Sync up rewards for a discount.
     *
     * @param Discount $discount
     * @param array $rewards
     *
     * @return void
     */
    public function syncRewards($discount, array $rewards)
    {
        foreach ($rewards as $reward) {
            $model = $discount->rewards()->create($reward);
            if (! empty($reward['products'])) {
                foreach ($reward['products'] as $productReward) {
                    $model->products()->create($productReward);
                }
            }
        }

        return $discount;
    }

    /**
     * Get All the discounts.
     *
     * @return array
     */
    public function get()
    {
        return $this->model->orderBy('priority', 'desc')->with(['sets', 'sets.items'])->get();
    }

    public function getByCoupon($coupon)
    {
        return DiscountCriteriaItem::where('value', '=', $coupon)->first();
    }

    public function parse($discounts)
    {
        $sets = [];
        foreach ($discounts as $index => $discount) {
            $factory = new DiscountFactory();
            $factory->setModel($discount);
            $factory->stop = $discount->stop_rules;

            $rewardSet = new RewardSet;

            foreach ($discount->rewards as $reward) {
                $rewardSet->add([
                    'type' => $reward->type,
                    'value' => $reward->value,
                ]);
            }

            $factory->setReward($rewardSet);

            foreach ($discount->sets as $set) {
                $criteriaSet = new \GetCandy\Api\Core\Discounts\CriteriaSet;
                $criteriaSet->scope = $set['scope'];
                $criteriaSet->outcome = $set['outcome'];
                foreach ($set->items as $item) {
                    $criteriaSet->add($item);
                }
                $sets[] = $factory->addCriteria($criteriaSet);
            }
        }

        return collect($sets);
    }

    public function getFactory($discount)
    {
        $factory = new DiscountFactory();
        $factory->setModel($discount);
        $factory->stop = $discount->stop_rules;

        $rewardSet = new RewardSet;

        foreach ($discount->rewards as $reward) {
            $rewardSet->add([
                'type' => $reward->type,
                'value' => $reward->value,
            ]);
        }

        $factory->setReward($rewardSet);

        foreach ($discount->sets as $set) {
            $criteriaSet = new \GetCandy\Api\Core\Discounts\CriteriaSet;
            $criteriaSet->scope = $set['scope'];
            $criteriaSet->outcome = $set['outcome'];
            foreach ($set->items as $item) {
                $criteriaSet->add($item);
            }
            $factory->addCriteria($criteriaSet);
        }

        return $factory;
    }
}
