<?php

namespace GetCandy\Api\Core\Currencies\Actions;

use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Currencies\Resources\CurrencyResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class CreateCurrency extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-currencies');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'code' => 'required|unique:currencies,code',
            'enabled' => 'required',
            'exchange_rate' => 'required',
            'format' => 'required',
            'decimal_point' => 'string',
            'thousand_point' => 'string',
            'default' => 'boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Currencies\Models\Currency
     */
    public function handle()
    {
        return Currency::create($this->validated())->load($this->resolveEagerRelations());
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Currencies\Models\Currency  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Currencies\Resources\CurrencyResource
     */
    public function response($result, $request)
    {
        return new CurrencyResource($result);
    }
}
