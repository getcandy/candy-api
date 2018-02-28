<?php

namespace GetCandy\Api\Http\Requests\Collections;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Collections\Models\Collection;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
        ];
    }
}
