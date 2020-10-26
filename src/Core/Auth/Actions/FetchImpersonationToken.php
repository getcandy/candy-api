<?php

namespace GetCandy\Api\Core\Auth\Actions;

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

    /**
     * Returns the response from the action.
     *
     * @param   array  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        return $this->respondWithArray([
            'access_token' => $result,
        ]);
    }
}
