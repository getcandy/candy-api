<?php

namespace GetCandy\Api\Http\Requests\Languages;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Http\Requests\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', Language::class);
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
            'name' => 'required',
            'lang' => 'required',
            'iso' => 'required|unique:languages,iso',
        ];
    }
}
