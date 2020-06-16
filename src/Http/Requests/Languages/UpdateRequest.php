<?php

namespace GetCandy\Api\Http\Requests\Languages;

use GetCandy\Api\Core\Languages\Models\Language;
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
        // return $this->user()->can('update', Language::class);
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Language $language)
    {
        return [
            'name' => 'required',
            'iso' => 'required|unique:languages,iso,'.$language->decodeId($this->id),
        ];
    }
}
