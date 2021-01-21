<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy;
use GetCandy\Api\Core\Customers\Actions\AttachUserToCustomer;
use GetCandy\Api\Core\Customers\Actions\CreateCustomer;
use GetCandy\Api\Core\Customers\Actions\DeleteCustomerInvite;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerInvite;
use GetCandy\Api\Core\Customers\Actions\FetchDefaultCustomerGroup;
use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use GetCandy\Api\Core\Languages\Actions\FetchEnabledLanguageByCode;
use GetCandy\Api\Core\Users\Resources\UserResource;
use Lorisleiva\Actions\Action;

class CreateUser extends Action
{
    /**
     * @var CustomerInvite|null
     */
    protected $invite;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->customer_id) {
            $this->invite = FetchCustomerInvite::run([
                'customer_id' => $this->customer_id,
                'email' => $this->email,
            ]);
            // TODO: Change to permission based auth
            if (! $this->invite && (! $this->user() || ! $this->user()->hasRole('admin'))) {
                return false;
            }
        }

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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'language' => 'nullable|string',
            'details' => 'nullable|array',
            'customer_groups' => 'nullable|array',
            'customer_id' => 'nullable|string',
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

        $user = (new $userModel);
        $user->name = $this->firstname.' '.$this->lastname;
        $user->email = $this->email;
        $user->password = bcrypt($this->password);

        $language = ! $this->language ? FetchDefaultLanguage::run() : FetchEnabledLanguageByCode::run([
            'code' => $this->language,
        ]);
        $user->language()->associate($language);
        $user->save();

        if ($this->customer_id) {
            AttachUserToCustomer::run([
                'encoded_id' => $this->customer_id,
                'user_id' => $user->encoded_id,
            ]);

            if ($this->invite) {
                DeleteCustomerInvite::run(['encoded_id' => $this->invite->encoded_id]);
            }
        }

        if (! $this->customer_id) {
            $defaultCustomer = FetchDefaultCustomerGroup::run();

            CreateCustomer::run([
                'user_id' => $user->encoded_id,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'fields' => $this->details['fields'] ?? [],
                'customer_group_ids' => [$defaultCustomer->encoded_id],
            ]);
        }

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
