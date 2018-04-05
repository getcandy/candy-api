<?php

namespace GetCandy\Api\Http\Requests\Languages;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Languages\Models\Language;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('delete', Language::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
        ];
    }
}
