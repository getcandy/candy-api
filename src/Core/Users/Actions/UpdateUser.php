<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy;
use GetCandy\Api\Core\Users\Resources\UserResource;
use Lorisleiva\Actions\Action;

class UpdateUser extends Action
{
    protected $userToUpdate;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $userModel = GetCandy::getUserModel();

        $this->userToUpdate = (new $userModel)->findOrFail((new $userModel)->decodeId($this->encoded_id));

        return $this->user() && (
            $this->user()->hasRole('admin') || $this->userToUpdate->id === $this->user()->id
        );
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
            'name' => 'string',
            'email' => 'email|unique:users,email,'.$this->userToUpdate->id,
            'password' => 'string|min:6|confirmed',
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
        $this->userToUpdate->name = $this->name ?? $this->userToUpdate->name;
        $this->userToUpdate->email = $this->email ?? $this->userToUpdate->name;
        if ($this->password) {
            $this->userToUpdate->password = bcrypt($this->password);
        }
        $this->userToUpdate->save();

        return $this->userToUpdate;
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
