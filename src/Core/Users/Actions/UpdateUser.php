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
            'details' => 'nullable|array',
            'customer_groups' => 'nullable|array',
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

        if ($this->details) {
            $details = $this->details;
            $details['firstname'] = $this->firstname ?? $user->firstname;
            $details['lastname'] = $this->lastname ?? $user->lastname;
            $details['fields'] = $this->details['fields'] ?? [];

            $user->customer()->update($details);
        }

        if ($this->customer_groups) {
            $user->customer->customerGroups()->sync(
                DecodeIds::run([
                    'model' => CustomerGroup::class,
                    'encoded_ids' => $this->customer_groups,
                ])
            );
        } else {
            $user->customer->customerGroups()->attach(FetchDefaultCustomerGroup::run());
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
