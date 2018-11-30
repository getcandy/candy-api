<?php

namespace GetCandy\Api\Http\Requests\Languages;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Languages\Models\Language;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('update', Language::class);
        return $this->user()->hasRole('admin');
    }

    public function rules(Language $language)
    {
        return [
            'name' => 'required',
            'iso' => 'required|unique:languages,iso,'.$language->decodeId($this->id),
        ];
    }
}
