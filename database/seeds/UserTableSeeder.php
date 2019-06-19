<?php

namespace Seeds;

use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Users\Models\UserDetail;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $language = app('api')->languages()->getDefaultRecord();

        $admin = User::create([
            'id' => 2,
            'name' => 'Thanos Balancer',
            'email' => 'perfectly@balanced.co.uk',
            'password' => \Hash::make('password'),
        ]);

        UserDetail::forceCreate([
            'user_id' => $admin->id,
            'title' => 'Lord',
            'firstname' => 'Thanos',
            'lastname' => 'Balancer',
        ]);
        $admin->language()->associate($language);
        $admin->save();

        $customerData = $this->customerData();
        $customer = User::create([
            'id' => 7,
            'name' => $customerData['firstname'],
            'email' => $customerData['email'],
            'password' => $customerData['password'],
        ]);

        $userDetail = $this->userDetail($customer, $customerData);
        UserDetail::forceCreate($userDetail);

        $shippingAddress = $this->addressData($customer, $customerData);
        $billingAddress = array_merge($shippingAddress, [
            'billing' => 1,
            'shipping' => 0,
        ]);
        Address::forceCreate($shippingAddress);
        Address::forceCreate($billingAddress);

        $group = CustomerGroup::find(2);

        $customer->groups()->attach($group->id);
        $customer->language()->associate($language);

        $customer->save();
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
            'user_id' => $customer->id,
            'firstname' => $customerData['firstname'],
            'lastname' => $customerData['lastname'],
            'address' => '24 Nice Place',
            'city' => 'London',
            'county' => 'London',
            'zip' => 'N1 1CE',
            'shipping' => 1,
            'billing' => 0,
            'default' => 0,
        ];
    }
}
