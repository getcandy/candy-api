<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy;
use GetCandy\Api\Core\Customers\Actions\AttachCustomerToGroups;
use GetCandy\Api\Core\Customers\Actions\FetchDefaultCustomerGroup;
use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Foundation\Actions\DecodeIds;
use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use GetCandy\Api\Core\Languages\Actions\FetchEnabledLanguageByCode;
use GetCandy\Api\Core\Users\Resources\UserResource;
use Lorisleiva\Actions\Action;

class CreateUser extends Action
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
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'language' => 'nullable|string',
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
        $userModel = config('auth.providers.users.model', User::class);

        $user = (new $userModel);
        $user->name = $this->firstname.' '.$this->lastname;
        $user->email = $this->email;
        $user->password = bcrypt($this->password);

        if (! $this->language) {
            $user->language()->associate(FetchDefaultLanguage::run());
        } else {
            $user->language()->associate(FetchEnabledLanguageByCode::run([
                'code' => $this->language
            ]));
        }

        $user->save();

        $details = $this->details;
        $details['firstname'] = $this->firstname;
        $details['lastname'] = $this->lastname;
        $details['fields'] = $this->details['fields'] ?? [];

        $customer = Customer::create($details);
        $customer->users()->save($user);

        if ($this->customer_groups) {
            $customer->customerGroups()->sync(
                DecodeIds::run([
                    'model' => CustomerGroup::class,
                    'encoded_ids' => $this->customer_groups,
                ])
            );
        } else {
            $customer->customerGroups()->attach(FetchDefaultCustomerGroup::run());
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
