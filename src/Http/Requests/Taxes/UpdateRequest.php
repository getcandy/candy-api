<?php

namespace GetCandy\Api\Http\Requests\Taxes;

use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('update', Tax::class);
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Tax $tax)
    {
        return [
            'name' => 'unique:taxes,name,'.$tax->decodeId($this->tax),
        ];
    }
}
