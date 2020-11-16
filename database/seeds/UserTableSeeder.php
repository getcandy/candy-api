<?php

namespace Seeds;

use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use Illuminate\Database\Seeder;
use Tests\Stubs\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $language = FetchDefaultLanguage::run();

        $admin = User::create([
            'id' => 2,
            'name' => 'Thanos Balancer',
            'email' => 'perfectly@balanced.co.uk',
            'password' => \Hash::make('password'),
        ]);

        $admin->customer()->create([
            'title' => 'Lord',
            'firstname' => 'Thanos',
            'lastname' => 'Balancer',
        ]);

        $admin->language()->associate($language);
        $admin->save();

        $customerData = $this->customerData();
        $user = User::create([
            'id' => 7,
            'name' => $customerData['firstname'],
            'email' => $customerData['email'],
            'password' => $customerData['password'],
        ]);

        $customer = Customer::create([
            'title' => $user['title'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
        ]);

        $user->update([
            'customer_id' => $customer->id,
        ]);

        $shippingAddress = $this->addressData($user, $customerData);
        $billingAddress = array_merge($shippingAddress, [
            'billing' => 1,
            'shipping' => 0,
        ]);
        Address::forceCreate($shippingAddress);
        Address::forceCreate($billingAddress);

        $group = CustomerGroup::find(2);

        $user->refresh()->customer->customerGroups()->attach($group->id);
        $user->language()->associate($language);

        $user->save();
    }

    /**
     * @return array
     */
    private function customerData()
    {
        return [
            'title' => 'Mr',
            'firstname' => 'Tony',
            'lastname' => 'Stark',
            'email' => 'me@starkindustries.com',
            'password' => \Hash::make('password'),
        ];
    }

    /**
     * @param User $customer
     * @param array $customerData
     * @return array
     */
    private function userDetail(User $customer, array $customerData)
    {
        return [
            'user_id' => $customer->id,
            'title' => $customerData['title'],
            'firstname' => $customerData['firstname'],
            'lastname' => $customerData['lastname'],
        ];
    }

    /**
     * @param User $customer
     * @param array $customerData
     * @return array
     */
    private function addressData(User $customer, array $customerData)
    {
        return [
            'addressable_type' => get_class($customer),
            'addressable_id' => $customer->id,
            'firstname' => $customerData['firstname'],
            'lastname' => $customerData['lastname'],
            'address' => '24 Nice Place',
            'city' => 'London',
            'state' => 'London',
            'postal_code' => 'N1 1CE',
            'shipping' => 1,
            'billing' => 0,
            'default' => 0,
        ];
    }
}
