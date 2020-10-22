<?php

namespace GetCandy\Api\Core\Users\Actions;

use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Action;

class ResetPassword extends Action
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
            'current_password' => 'string',
            'new_password' => 'string',
            'user' => '',
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

}
