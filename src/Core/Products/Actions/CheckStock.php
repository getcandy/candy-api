<?php

namespace GetCandy\Api\Core\Products\Actions;

use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Products\Actions\FetchStock;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class CheckStock extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'variant_id' => 'required_without:sku|hashid_is_valid:product_variants',
            'sku' => 'required_without:variant_id',
            'basket_id' => 'sometimes|hashid_is_valid:baskets',
            'order_id' => 'sometimes|hashid_is_valid:orders',
            'qty' => 'numeric|min:1'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Products\Models\ProductFamily
     */
    public function handle()
    {
        // return false;
        if ($this->sku) {
            $variant = ProductVariant::whereSku($this->sku)->first();
        }

        if ($this->variant_id) {
            $realId = (new ProductVariant)->decodeId(
                $this->variant_id
            );
            $variant = ProductVariant::find($realId);
        }


        if (!$variant) {
            return false;
        }

        $backorder = $variant->backorder;

        $stock = FetchStock::run([
            'sku' => $variant->sku,
        ]);

        if ($backorder == 'always') {
            return true;
        }

        $order = null;

        if ($this->basket_id) {
            $realBasketId = (new Basket)->decodeId($this->basket_id);
            $basket = Basket::find($realBasketId);
            if ($basket && $basket->activeOrder) {
                $this->set('order_id', $basket->activeOrder->encoded_id);
            }
        }

        if ($this->order_id) {
            $realOrderId = (new Order)->decodeId($this->order_id);
            $order = Order::find($realOrderId);
            if ($order && $order->expires_at->isFuture()) {
                $line = $order->lines->first(function ($line) use ($variant) {
                    return $line->sku === $variant->sku;
                });
                if ($line) {
                    $stock += $line->quantity;
                }
            }
        }

        if ($backorder == 'expected') {
            return ($variant->incoming + $stock) >= $this->quantity;
        }
        return $this->quantity <= $stock;
    }
}
