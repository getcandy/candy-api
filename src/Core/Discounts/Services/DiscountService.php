<?php

namespace GetCandy\Api\Core\Discounts\Services;

use Carbon\Carbon;
use GetCandy;
use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Discounts\Discount as DiscountFactory;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaSet;
use GetCandy\Api\Core\Discounts\RewardSet;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\BaseService;

class DiscountService extends BaseService
{
    public function __construct()
    {
        $this->model = new Discount();
    }

    /**
     * Create a discount.
     *
     * @param  array  $data
     *
     * @return \GetCandy\Api\Core\Discounts\Models\Discount
     */
    public function create(array $data)
    {
        $discount = new Discount();
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

        if (! empty($data['channels'])) {
            $discount->channels()->sync(
                $this->getChannelMapping($data['channels'])
            );
        } else {
            $discount->channels()->sync(Channel::select('id')->get()->mapWithKeys(function ($c) {
                return [$c->id => [
                    'published_at' => null,
                ]];
            })->toArray());
        }

        event(new AttributableSavedEvent($discount));

        return $discount;
    }

    /**
     * Update an existing discount.
     *
     * @param  string  $id
     * @param  array  $data
     *
     * @return \GetCandy\Api\Core\Discounts\Models\Discount
     */
    public function update($id, array $data)
    {
        $discount = $this->getByHashedId($id);
        $discount->start_at = Carbon::parse($data['start_at']);
        $discount->end_at = Carbon::parse($data['end_at']);
        $discount->priority = $data['priority'];
        $discount->stop_rules = $data['stop_rules'];
        $discount->status = $data['status'];
        $discount->attribute_data = $data['attribute_data'];
        $discount->save();
        // event(new AttributableSavedEvent($discount));
        if (isset($data['rewards'])) {
            $discount->rewards->each(function ($reward) {
                $reward->products()->delete();
                $reward->delete();
            });
            $this->syncRewards($discount, $data['rewards']);
        }

        if (! empty($data['channels'])) {
            $discount->channels()->sync(
                $this->getChannelMapping($data['channels'])
            );
        }

        if (! empty($data['sets'])) {
            $this->syncSets($discount, $data['sets']);
        }

        return $discount;
    }

    /**
     * Set up sets and rewards with a discount.
     *
     * @param  \GetCandy\Api\Core\Discounts\Models\Discount  $discount
     * @param  array  $sets
     *
     * @return \GetCandy\Api\Core\Discounts\Models\Discount
     */
    public function syncSets($discount, array $sets)
    {
        $setIds = [];

        foreach ($sets as $set) {
            if (! empty($set['id'])) {
                $id = (new DiscountCriteriaSet())->decodeId($set['id']);
                $setModel = DiscountCriteriaSet::find($id);
            } else {
                $setModel = $discount->sets()->create([
                    'scope' => $set['scope'],
                    'outcome' => (bool) $set['outcome'],
                ]);
            }

            $setIds[] = $setModel->id;

            if (! empty($set['items'])) {
                $set['items'] = $set['items'];
            }

            $itemIds = [];

            foreach ($set['items'] as $item) {
                if (! empty($item['id'])) {
                    $modelId = (new DiscountCriteriaItem())->decodeId($item['id']);
                    $model = DiscountCriteriaItem::find($modelId);
                    $model->fill($item);
                    $model->save();
                } else {
                    $model = $setModel->items()->create($item);
                }
                $itemIds[] = $model->id;
                if (! empty($item['eligibles'])) {
                    switch ($item['type']) {
                        case 'product':
                            $realIds = (new Product())->decodeIds($item['eligibles']);

                        break;
                        default:
                            $userModel = GetCandy::getUserModel();
                            $realIds = (new $userModel())->decodeIds($item['eligibles']);

                        break;
                    }
                    $model->saveEligibles($item['type'], $realIds);
                }
            }
            $setModel->refresh()->items->filter(function ($item) use ($itemIds) {
                return ! in_array($item->id, $itemIds);
            })->each(function ($item) {
                $item->products()->delete();
                $item->customerGroups()->delete();
                $item->users()->delete();
                $item->delete();
            });
        }

        $discount->refresh()->sets->filter(function ($set) use ($setIds) {
            return ! in_array($set->id, $setIds);
        })->each(function ($set) {
            $set->items->each(function ($item) {
                $item->products()->delete();
                $item->customerGroups()->delete();
                $item->users()->delete();
                $item->delete();
            });
            $set->delete();
        });

        return $discount;
    }

    /**
     * Sync up rewards for a discount.
     *
     * @param  \GetCandy\Api\Core\Discounts\Models\Discount  $discount
     * @param  array  $rewards
     *
     * @return \GetCandy\Api\Core\Discounts\Models\Discount
     */
    public function syncRewards($discount, array $rewards)
    {
        foreach ($rewards as $reward) {
            $model = $discount->rewards()->create($reward);
            if (! empty($reward['products'])) {
                foreach ($reward['products'] as $productReward) {
                    $productReward['product_id'] = (new Product())->decodeId($productReward['product_id']);
                    $model->products()->create($productReward);
                }
            }
        }

        return $discount;
    }

    /**
     * Get All the discounts.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get()
    {
        return $this->model->orderBy('priority', 'desc')->with(['sets', 'sets.items'])->get();
    }

    /**
     * Returns model by a given hashed id.
     *
     * @param  string  $id
     * @param  array  $relations
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \GetCandy\Api\Core\Discounts\Models\Discount
     */
    public function getByHashedId($id, $relations = null)
    {
        $id = $this->model->decodeId($id);

        // TODO: Probably need a better way to do this.
        $query = $this->model->withoutGlobalScopes();

        if ($relations) {
            $query->with($relations);
        }

        return $query->findOrFail($id);
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

            $rewardSet = new RewardSet();

            foreach ($discount->rewards as $reward) {
                $rewardSet->add([
                    'type' => $reward->type,
                    'value' => $reward->value,
                ]);
            }

            $factory->setReward($rewardSet);

            foreach ($discount->sets as $set) {
                $criteriaSet = new \GetCandy\Api\Core\Discounts\CriteriaSet();
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

        $rewardSet = new RewardSet();

        foreach ($discount->rewards as $reward) {
            $rewardSet->add([
                'type' => $reward->type,
                'value' => $reward->value,
            ]);
        }

        $factory->setReward($rewardSet);

        foreach ($discount->sets as $set) {
            $criteriaSet = new \GetCandy\Api\Core\Discounts\CriteriaSet();
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
