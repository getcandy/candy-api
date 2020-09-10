<?php

namespace GetCandy\Api\Core\Currencies\Actions;

use GetCandy\Api\Core\Exceptions\DefaultRecordRequiredException;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;

class DeleteCurrency extends AbstractAction
{
    use ReturnsJsonResponses;

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
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return bool
     */
    public function handle()
    {
        $currency = FetchCurrency::run([
            'encoded_id' => $this->encoded_id,
        ]);
        if ($currency->default) {
            if (! $this->runningAs('controller')) {
                throw new DefaultRecordRequiredException;
            }

            return false;
        }

        return $currency->delete();
    }

    /**
     * Returns the response from the action.
     *
     * @param   bool
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorUnprocessable('You cannot remove the default record.');
        }

        return $this->respondWithNoContent();
    }
}
