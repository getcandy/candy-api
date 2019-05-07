<?php

namespace GetCandy\Api\Http\Resources\Users;

use GetCandy\Api\Http\Resources\AbstractResource;

class UserDetailsResource extends AbstractResource
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
        ];
    }
}
