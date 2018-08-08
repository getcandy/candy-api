<?php

namespace GetCandy\Api\Core\Products\Factories;

use PriceCalculator;
use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface;

class ProductVariantFactory implements ProductVariantInterface
{
    /**
     * The variant.
     *
     * @var ProductVariant
     */
    protected $variant;

    public function init(ProductVariant $variant)
    {
        $this->variant = $variant;

        return $this;
    }

    public function get($qty = 1, Model $user = null)
    {
        // Lock the variant from saving.
        $this->variant->lock();

        $tieredPrice = $this->getTieredPrice($qty, $user);
        $variantPrice = $this->getVariantPrice($qty, $user);
        $basePrice = $variantPrice->base_cost;
        $unitCost = $variantPrice->unit_cost;
        $unitTax = $variantPrice->unit_tax;
        $totalCost = $variantPrice->total_cost;
        $totalTax = $variantPrice->total_tax;
        $basePrice = $variantPrice->base_cost;

        if ($tieredPrice) {
            $basePrice = $tieredPrice->base_cost;
            $unitCost = $tieredPrice->unit_cost;
            $unitTax = $tieredPrice->unit_tax;
            $totalCost = $tieredPrice->total_cost;
            $totalTax = $tieredPrice->total_tax;
            $basePrice = $tieredPrice->base_cost;
        }

        $this->variant->qty = $qty;
        $this->variant->price = $basePrice;
        $this->variant->unit_tax = $unitTax;
        $this->variant->unit_cost = $unitCost;
        $this->variant->total_tax = $totalTax;
        $this->variant->base_cost = $basePrice;
        $this->variant->total_price = $totalCost;
        $this->variant->origial_price = $this->variant->price;

        return $this->variant;
    }

    /**
     * Get tiered price for variant.
     *
     * @param int $quantity
     * @param mixed $user
     * @return \Illuminate\Support\Collection
     */
    public function getTieredPrice($qty = 1, $factor = 1, $user = null)
    {
        $groups = \GetCandy::getGroups();

        $ids = [];

        foreach ($groups as $group) {
            $ids[] = $group->id;
        }

        $price = $this->variant->tiers->whereIn('customer_group_id', $ids)
            ->where('lower_limit', '<=', $qty)
            ->sortBy('price')
            ->first();

        if (! $price) {
            return;
        }

        $tax = 0;

        if ($this->variant->tax) {
            $taxRate = $this->variant->tax->percentage;
        }

        return PriceCalculator::get(
            $price->price,
            $taxRate,
            $qty,
            $this->variant->unit_qty
        );
    }

    /**
     * Get the variant price for a user.
     *
     * @param mixed $user
     * @return \Illuminate\Support\Collection
     */
    public function getVariantPrice($qty = 1, $user = null)
    {
        $groups = \GetCandy::getGroups();

        $ids = [];

        foreach ($groups as $group) {
            $ids[] = $group->id;
        }

        $pricing = null;

        // If the user is an admin, fall through
        if (! $user || ($user && ! $user->hasRole('admin'))) {
            $pricing = $this->variant->customerPricing
                ->whereIn('customer_group_id', $ids)
                ->sortBy('price')->first();
        }

        $taxRate = $this->variant->tax->percentage ?? 0;
        $price = $this->variant->price;

        if ($pricing) {
            $taxRate = $pricing->tax->percentage ?? 0;
            $price = $pricing->price;
        }

        return PriceCalculator::get(
            $price,
            $taxRate,
            $qty,
            $this->variant->unit_qty
        );
    }
}
