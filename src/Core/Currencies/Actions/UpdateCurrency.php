<?php

namespace GetCandy\Api\Core\Currencies\Actions;

use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Currencies\Resources\CurrencyResource;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class UpdateCurrency extends AbstractAction
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
    public function rules(): array
    {
        $currencyId = DecodeId::run([
            'encoded_id' => $this->encoded_id,
            'model' => Currency::class,
        ]);

        return [
            'name' => 'nullable',
            'code' => 'nullable|unique:currencies,code,'.$currencyId,
            'enabled' => 'nullable|boolean',
            'exchange_rate' => 'nullable|string',
            'format' => 'nullable|string',
            'decimal_point' => 'nullable|string',
            'thousand_point' => 'nullable|string',
            'default' => 'nullable|boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Currencies\Models\Currency
     */
    public function handle()
    {
        $currency = $this->delegateTo(FetchCurrency::class);
        $currency->update($this->validated());

        return $currency;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Customers\Models\Customer  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Currencies\Resources\CurrencyResource
     */
    public function response($result, $request)
    {
        return new CurrencyResource($result);
    }
}
