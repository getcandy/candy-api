<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Users;

use GetCandy\Api\Core\Users\Models\UserDetail;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class UserDetailsTransformer extends BaseTransformer
{
    public function transform(UserDetail $details)
    {
        return [
            'title' => $details->title,
            'firstname' => $details->firstname,
            'lastname' => $details->lastname,
            'company_name' => $details->company_name,
            'contact_number' => $details->contact_number,
            'alt_contact_number' => $details->alt_contact_number,
            'vat_no' => $details->vat_no,
            'fields' => $details->fields,
        ];
    }
}
