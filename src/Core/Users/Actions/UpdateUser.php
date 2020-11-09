<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy;
use GetCandy\Api\Core\Customers\Actions\FetchDefaultCustomerGroup;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Foundation\Actions\DecodeIds;
use GetCandy\Api\Core\Users\Resources\UserResource;
use Lorisleiva\Actions\Action;

class UpdateUser extends Action
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
            'encoded_id' => 'required|string|hashid_is_valid:'.GetCandy::getUserModel(),
            'firstname' => 'string',
            'lastname' => 'string',
            'email' => 'email',
            'password' => 'string|min:6',
            'language' => 'string',
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

        $user = (new $userModel)->findOrFail((new $userModel)->decodeId($this->encoded_id));

        $user->email = $this->email ?? $user->email;
        if ($this->password) {
            $user->password = bcrypt($this->password);
        }
        $user->save();

        return $user;
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
