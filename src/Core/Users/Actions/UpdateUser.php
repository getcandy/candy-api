<?php

namespace GetCandy\Api\Core\Users\Actions;

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
            'encoded_id' => '',
            'firstname' => 'string',
            'lastname' => 'string',
            'email' => 'email',
            'password' => '', // specifics on password requirements?
            'language' => '',
            'details' => '',
            'customer_groups' => '',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = FetchUser::run([
            'encoded_id' => $this->encoded_id,
        ]);

        $user->email = $this->email ?? $user->email;
        if ($this->password) {
            $user->password = bcrypt($this->password);
        }

        // abstract to CreateDetails
        if ($this->details) {
            $this->details['firstname'] = $this->firstname;
            $this->details['lastname'] = $this->lastname;
            $this->details['fields'] = $this->details['fields'] ?? [];
            $this->details['user_id'] = $user->id;

            $user->details()->updateOrCreate(
                ['user_id' => $user->id],
                $this->details
            );
        }

        if ($this->customer_groups) {
            $user->groups()->sync(
                DecodeIds::run([
                    'model' => CustomerGroup::class,
                    'encoded_ids' => $this->customer_groups,
                ])
            );
        } else {
            $user->groups()->attach(FetchDefaultCustomerGroup::run());
        }

        $user->save();

        return $user;
    }

    /**
     * Returns the response from the action
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
