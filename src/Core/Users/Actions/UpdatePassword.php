<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Action;

class UpdatePassword extends Action
{
    use ReturnsJsonResponses;

    /**
     * Determine if the user is authmorized to make this action.
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
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'user' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! Hash::check($this->current_password, $this->user->password)) {
            return false;
        }

        $this->user->password = bcrypt($this->new_password);
        $this->user->save();

        return $this->user;
    }

    /**
     * Returns the response from the action.
     *
     * @param $result
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorForbidden();
        }

        return $this->respondWithSuccess('Password changed');
    }
}
