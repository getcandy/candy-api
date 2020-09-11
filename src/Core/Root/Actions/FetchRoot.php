<?php

namespace GetCandy\Api\Core\Root\Actions;

use GetCandy;
use GetCandy\Api\Core\Channels\Actions\FetchCurrentChannel;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Http\Resources\Currencies\CurrencyResource;

class FetchRoot extends AbstractAction
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
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Languages\Models\Language
     */
    public function handle(CurrencyConverterInterface $currency)
    {
        return [
            'version' => GetCandy::version(),
            'locale' => app()->getLocale(),
            'channel' => $this->delegateTo(FetchCurrentChannel::class),
            'currency' => new CurrencyResource($currency->get()),
        ];
    }

    /**
     * Returns the response from the action.
     *
     * @param   array  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        return response()->json($result);
    }
}
