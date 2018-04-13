<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Users;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Users\Models\UserDetail;

class UserDetailsTransformer extends BaseTransformer
{
    public function transform(UserDetail $details)
    {
        return [
            'title'          => $details->title,
            'firstname'      => $details->firstname,
            'lastname'       => $details->lastname,
            'company_name'   => $details->company_name,
            'contact_number' => $details->contact_number,
            'vat_no'         => $details->vat_no,
        ];
    }
}
