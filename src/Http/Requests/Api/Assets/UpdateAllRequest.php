<?php

namespace GetCandy\Api\Http\Requests\Assets;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateAllRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('delete', Attribute::class);
        return true;
    }

    public function rules()
    {
        return [
            'assets' => 'required|array',
            'assets.*.tags' => 'array',
            'assets.*.tags.*.name' => 'required_without:assets.*.tags.*.id',
            'assets.*.tags.*.id' => 'required_without:assets.*.tags.*.name',
        ];
    }
}
