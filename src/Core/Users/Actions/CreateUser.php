<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy\Api\Core\Customers\Actions\FetchDefaultCustomerGroup;
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
            'password' => 'required', // specifics on password requirements?
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

        // abstract to CreateDetails
        $this->details['firstname'] = $this->firstname;
        $this->details['lastname'] = $this->lastname;
        $this->details['fields'] = $this->details['fields'] ?? [];
        $this->details['user_id'] = $user->id;
        $user->details()->create($this->details);

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
