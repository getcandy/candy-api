<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Users\Resources\UserResource;

class FetchCurrentUser extends AbstractAction
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
        return $this->user()->load($this->resolveEagerRelations());
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
