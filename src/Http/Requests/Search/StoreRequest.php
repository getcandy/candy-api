<?php

namespace GetCandy\Api\Http\Requests\Search;

use GetCandy\Api\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', Product::class);
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'keywords' => 'required_without:filters',
            'type' => 'required',
            'filters' => 'required_without:keywords',
        ];
    }
}
