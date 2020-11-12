<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Users\Resources\UserResource;

class FetchUser extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('view-users');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'integer|exists:users,id|required_without_all:encoded_id,email',
            'encoded_id' => 'string|hashid_is_valid:'.GetCandy::getUserModel().'|required_without_all:id,email',
            'email' => 'email|required_without_all:id,encoded_id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $userModel = GetCandy::getUserModel();
        if ($this->encoded_id) {
            $this->id = (new $userModel)->decodeId($this->encoded_id);
        }

        if ($this->email) {
            return (new $userModel)->where('email', '=', $this->email)->firstOrFail();
        }

        return (new $userModel)->with($this->resolveEagerRelations())->findOrFail($this->id);
    }

    /**
     * Returns the response from the action.
     *
     * @param $result
     * @param \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Core\Users\Resources\UserResource
     */
    public function response($result, $request)
    {
        return new UserResource($result);
    }
}
