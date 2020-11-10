<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy\Api\Core\Users\Resources\UserResource;
use Lorisleiva\Actions\Action;

class FetchCurrentUser extends Action
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
     * @return mixed
     */
    public function handle()
    {
        return $this->user()->load([
            'addresses.country', 'roles.permissions', 'customer',
        ]);
    }

    /**
     * Returns the response from the action.
     *
     * @param   array  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return \GetCandy\Api\Core\Users\Resources\UserResource;
     */
    public function response($result, $request)
    {
        return new UserResource($result);
    }
}
