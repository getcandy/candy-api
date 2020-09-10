<?php

namespace GetCandy\Api\Core\Currencies\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Channels\Resources\ChannelResource;

class FetchDefaultCurrency extends AbstractAction
{
    /**
     * The fetched address model.
     *
     * @var \GetCandy\Api\Core\Currencies\Models\Currency
     */
    protected $currency;

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
    public function rules(): array
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Currencies\Models\Currency
     */
    public function handle()
    {
        return Currency::with($this->resolveEagerRelations())->whereDefault(true)->first();
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
        if (! $result) {
            return $this->errorNotFound();
        }

        return new ChannelResource($result);
    }
}
