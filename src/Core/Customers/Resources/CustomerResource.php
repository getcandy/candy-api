<?php

namespace GetCandy\Api\Core\Customers\Resources;

use GetCandy\Api\Core\Addresses\Resources\AddressCollection;
use GetCandy\Api\Core\Users\Resources\UserCollection;
use GetCandy\Api\Http\Resources\AbstractResource;

class CustomerResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'title' => $this->title,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'company_name' => $this->company_name,
            'contact_number' => $this->contact_number,
            'alt_contact_number' => $this->alt_contact_number,
            'vat_no' => $this->vat_no,
            'fields' => $this->fields,
            $this->mergeWhen($this->users_count !== null, [
                'users_count' => $this->users_count,
            ]),
        ];
    }

    public function includes()
    {
        return [
            'customer_groups' => new CustomerGroupCollection($this->whenLoaded('customerGroups')),
            'users' => new UserCollection($this->whenLoaded('users')),
            'addresses' => new AddressCollection($this->whenLoaded('addresses')),
        ];
    }
}
