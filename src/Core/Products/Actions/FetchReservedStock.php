<?php

namespace GetCandy\Api\Core\Products\Actions;

use GetCandy\Api\Core\Orders\Models\OrderLine;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Products\Models\ProductFamily;

class FetchReservedStock extends AbstractAction
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
            'sku' => 'required'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Products\Models\ProductFamily
     */
    public function handle()
    {
        // Get reserved stock.
        return OrderLine::whereSku($this->sku)->whereHas('order', function ($query) {
            $query->whereNull('placed_at')
            ->where('expires_at', '>', now());
        })->sum('quantity');
    }
}
