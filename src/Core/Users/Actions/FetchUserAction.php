<?php

namespace GetCandy\Api\Core\Users\Actions;

use App\User;
use GetCandy;
use Lorisleiva\Actions\Action;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Countries\Models\Country;

class FetchUserAction extends Action
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
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $userModel = config('auth.providers.users.model', User::class);
        if ($this->encoded_id) {
            $this->id = (new $userModel)->decodeId($this->encoded_id);
        }
        return (new $userModel)->findOrFail($this->id);
    }
}
