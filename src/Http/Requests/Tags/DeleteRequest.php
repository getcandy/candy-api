<?php

namespace GetCandy\Api\Http\Requests\Tags;

use Auth;
use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Tags\Models\Tag;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }
    public function rules()
    {
        return [];
    }
}
