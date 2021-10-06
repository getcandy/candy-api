<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy\Api\Core\Addresses\Resources\AddressCollection;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Lorisleiva\Actions\Action;

class FetchUserAddresses extends Action
{
    use ReturnsJsonResponses;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->user_id) {
            return $this->user()->can('manage-users');
        }

        return $this->user();
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'nullable',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->user_id) {
            return FetchUser::run([
                'encoded_id' => $this->user_id,
                'include' => 'addresses.country',
            ])->addresses;
        }

        return $this->user()->load('addresses.country')->addresses;
    }

    /**
     * Returns the response from the action.
     *
     * @param $result
     * @param \Illuminate\Http\Request  $request
     *
     * @return \GetCandy\Api\Core\Users\Resources\UserResource
     */
    public function response($result, $request)
    {
        return new AddressCollection($result);
    }
}
