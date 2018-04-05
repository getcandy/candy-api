<?php

namespace GetCandy\Api\Http\Requests\Taxes;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Taxes\Models\Tax;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('delete', Tax::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
        ];
    }
}
