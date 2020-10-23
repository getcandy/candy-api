<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy;
use Lorisleiva\Actions\Action;

class FetchImpersonationToken extends Action
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
            'id' => 'integer|exists:users|required_without:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:'.GetCandy::getUserModel().'|required_without:id',
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

        $user = (new $userModel)->findOrFail($this->id);

        return $user->createToken(str_random(25));
    }
}
