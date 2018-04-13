<?php

namespace GetCandy\Api\Products\Services;

use GetCandy\Api\Products\Models\ProductVariant;
use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Search\Events\IndexableSavedEvent;
use PriceCalculator;

class ProductVariantService extends BaseService
{
    public function __construct()
    {
        $this->model = new ProductVariant();
    }

    /**
     * Creates variants for a product.
     *
     * @param string $id
     * @param array  $variant
     *
     * @return bool
     */
    public function create($id, array $data)
    {
        $product = app('api')->products()->getByHashedId($id);

        // If we are adding a new set of variants, get rid.

        if ($product->variants->count() == 1) {
            foreach ($product->variants as $variant) {
                $variant->basketLines()->delete();
                $variant->customerPricing()->delete();
                $variant->tiers()->delete();
            }
            $product->variants()->delete();
        }

        $options = $product->option_data;

        if (empty($options)) {
            $product->update([
                'option_data' => $data['options'],
            ]);
            $options = $data['options'];
        }

        foreach ($data['variants'] as $newVariant) {
            $options = $this->mapOptions($options, $newVariant['options']);
            $sku = $newVariant['sku'];
            $i = 1;
            while (app('api')->productVariants()->existsBySku($sku)) {
                $sku = $sku.$i;
                $i++;
            }

            $variant = $product->variants()->create([
                'price'   => $newVariant['price'],
                'sku'     => $sku,
                'stock'   => $newVariant['inventory'],
                'options' => $newVariant['options'],
            ]);

            if (!empty($newVariant['tax_id'])) {
                $variant->tax()->associate(
                    app('api')->taxes()->getByHashedId($newVariant['tax_id'])
                );
            } else {
                $variant->tax()->associate(
                app('api')->taxes()->getDefaultRecord()
            );
            }

            $this->setMeasurements($variant, $newVariant);

            $variant->save();

            if (!empty($newVariant['pricing'])) {
                $this->setGroupPricing($variant, $newVariant['pricing']);
            }

            if (!empty($newVariant['tiers'])) {
                $this->setPricingTiers($variant, $newVariant['tiers']);
            }
        }

        if (empty($data['options'])) {
            $product->update([
                'option_data' => $options,
            ]);
        }

        return $product;
    }

    /**
     * Gets any tiered pricing for this variant.
     *
     * @param ProductVariant $variant
     * @param int            $quantity
     * @param mixed          $user
     *
     * @return void
     */
    public function getTieredPrice($variant, $quantity, $user = null)
    {
        $groups = \GetCandy::getGroups();

        $ids = [];

        foreach ($groups as $group) {
            $ids[] = $group->id;
        }

        $price = $variant->tiers()->whereIn('customer_group_id', $ids)
            ->where('lower_limit', '<=', $quantity)
            ->orderBy('price', 'asc')
            ->first();

        if (!$price) {
            return;
        }

        $tax = 0;

        if ($variant->tax) {
            $tax = $variant->tax->percentage;
        }

        return PriceCalculator::get($price->price, $tax);
    }

    public function canAddToBasket($variantId, $quantity)
    {
        $variant = $this->getByHashedId($variantId);

        return $variant->backorder || $quantity <= $variant->stock;
    }

    /**
     * Gets the variants true price.
     *
     * @param ProductVariant $variant
     * @param mixed          $user
     *
     * @return void
     */
    public function getVariantPrice($variant, $user = null)
    {
        // clock()->startEvent($variant->encodedId(), "Getting variant [{$variant->encodedId()}] price");
        $groups = \GetCandy::getGroups();

        $ids = [];

        foreach ($groups as $group) {
            $ids[] = $group->id;
        }

        $pricing = null;

        // If the user is an admin, fall through
        if (!$user || ($user && !$user->hasRole('admin'))) {
            $pricing = $variant->customerPricing()
                ->whereIn('customer_group_id', $ids)
                ->orderBy('price', 'asc')
                ->first();
        }

        if ($pricing) {
            $tax = $pricing->tax ? $pricing->tax->percentage : 0;
            $price = $pricing->price;
        } else {
            $tax = 0;
            $price = $variant->price;
            if ($variant->tax) {
                $tax = $variant->tax->percentage;
            }
        }

        $price = PriceCalculator::get($price, $tax);

        return $price;
    }

    /**
     * Checks whether a variant exists by its SKU.
     *
     * @param string $sku
     *
     * @return void
     */
    public function existsBySku($sku)
    {
        return $this->model->where('sku', '=', $sku)->exists();
    }

    /**
     * Updates a resource from the given data.
     *
     * @param string $hashedId
     * @param array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception
     *
     * @return GetCandy\Api\Models\ProductVariant
     */
    public function update($hashedId, array $data)
    {
        $variant = $this->getByHashedId($hashedId);

        $options = $variant->product->option_data;

        if (!empty($data['options'])) {
            $variant->product->update([
                'option_data' => $this->mapOptions($options, $data['options']),
            ]);
        }

        $variant->fill($data);

        $thumbnailId = null;

        if (!empty($data['thumbnail'])) {
            $thumbnailId = $data['thumbnail']['data']['id'];
        } elseif (!empty($data['thumbnail_id'])) {
            $thumbnailId = $data['thumbnail_id'];
        }

        if ($thumbnailId) {
            $asset = app('api')->assets()->getByHashedId($thumbnailId);
            $variant->image()->associate($asset);
        }

        if (!empty($data['tax_id'])) {
            $variant->tax()->associate(
                app('api')->taxes()->getByHashedId($data['tax_id'])
            );
        } else {
            $variant->tax()->dissociate();
        }

        $this->setMeasurements($variant, $data);

        if (isset($data['group_pricing']) && !$data['group_pricing']) {
            $variant->customerPricing()->delete();
        }
        if (isset($data['inventory'])) {
            $variant->stock = $data['inventory'];
        }

        if (!empty($data['pricing'])) {
            $this->setGroupPricing($variant, $data['pricing']);
        }

        if (!empty($data['tiers'])) {
            $this->setPricingTiers($variant, $data['tiers']);
        }

        $variant->save();

        event(new IndexableSavedEvent($variant->product));

        return $variant;
    }

    /**
     * Sets and creates the customer group pricing.
     *
     * @param array $variant
     * @param array $prices
     *
     * @return void
     */
    protected function setGroupPricing($variant, $prices = [])
    {
        $variant->customerPricing()->delete();

        foreach ($prices as $price) {
            $price['customer_group_id'] = app('api')->customerGroups()->getDecodedId($price['customer_group_id']);

            if (!empty($price['tax_id'])) {
                $price['tax_id'] = app('api')->taxes()->getDecodedId($price['tax_id']);
            } else {
                $price['tax_id'] = null;
            }

            $variant->customerPricing()->create($price);
        }
    }

    protected function setPricingTiers($variant, $tiers = [])
    {
        $variant->tiers()->delete();

        foreach ($tiers as $tier) {
            $tier['customer_group_id'] = app('api')->customerGroups()->getDecodedId($tier['customer_group_id']);
            $variant->tiers()->create($tier);
        }
    }

    /**
     * Map and merge variant options.
     *
     * @param array $options
     * @param array $newOptions
     *
     * @return array
     */
    protected function mapOptions($options, $newOptions)
    {
        foreach ($newOptions as $handle => $option) {
            foreach ($option as $lang => $value) {
                $optionKey = str_slug($value);
                // If this is the first time this option is being set...
                if (empty($options[$handle])) {
                    $options[$handle]['label'][$lang] = title_case($value);
                }
                $options[$handle]['options'][$optionKey]['values'][$lang] = $value;
            }
        }

        return $options;
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param string $id
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return bool
     */
    public function delete($hashedId)
    {
        $variant = $this->getByHashedId($hashedId);

        if (!$variant) {
            abort(404);
        }

        $variant->customerPricing()->delete();
        $variant->tiers()->delete();
        $variant->basketLines()->delete();

        return $variant->delete();
    }

    /**
     * Maps and sets the measurements for a variant.
     *
     * @param ProductVariant $variant
     *                                [
     *                                'weight' => [
     *                                'cm' => 100
     *                                ]
     *                                ]
     * @param array          $data
     */
    protected function setMeasurements($variant, $data)
    {
        $measurements = ['weight', 'height', 'width', 'depth', 'volume'];

        array_map(function ($x) use ($data, $variant) {
            if (!empty($data[$x])) {
                foreach ($data[$x] as $label => $value) {
                    $variant->setAttribute($x.'_'.$label, is_numeric($value) ? $value : $value);
                }
            }
        }, $measurements);
    }
}
