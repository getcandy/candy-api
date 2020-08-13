<?php

namespace GetCandy\Api\Core\Addresses\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Countries\Models\Country;

class AddressService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\Addresses\Models\Address
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Address;
    }

    /**
     * Checks whether an address already exists.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  array  $details
     * @param  string  $type
     * @return bool
     */
    public function exists($user, array $details, $type = 'billing')
    {
        // Fill it to make sure we only query against columns we actually have
        $address = new Address;
        $query = $this->model->where('user_id', '=', $user->id);
        foreach ($address->fill($details)->toArray() as $column => $value) {
            $query->where($column, '=', $value);
        }

        return $query->where($type, '=', true)->exists();
    }

    public function addAddress($user, $data, $type)
    {
        $data[$type] = true;

        return $this->create($user, $data);
    }

    public function update($id, array $data)
    {
        $address = $this->getByHashedId($id);
        if (!empty($data['country_id'])) {
            $realId = (new Country)->decodeId($data['country_id']);
            $country = Country::find($realId);
            $address->country_id = $country->id;
            unset($data['country_id']);
        }
        $address->fill($data);
        $address->save();

        return $address;
    }

    public function delete($address)
    {
        $address = $this->getByHashedId($address);

        return $address->delete();
    }

    /**
     * @param  string  $hashedAddressId
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function makeDefault(string $hashedAddressId): Address
    {
        $address = $this->getByHashedId($hashedAddressId);

        $this->removeAllDefaultForType($address->user_id, $address->type());

        $address->default = true;
        $address->save();

        return $address;
    }

    /**
     * @param  string  $hashedAddressId
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function removeDefault(string $hashedAddressId): Address
    {
        $address = $this->getByHashedId($hashedAddressId);

        $address->default = false;
        $address->save();

        return $address;
    }

    /**
     * @param  int  $userId
     * @param  string  $type - 'billing' or 'shipping'
     * @return void
     */
    private function removeAllDefaultForType(int $userId, string $type)
    {
        $this->model
            ->where('user_id', '=', $userId)
            ->where($type, '=', true)
            ->update(['default' => false]);
    }
}
