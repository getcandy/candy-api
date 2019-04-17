<?php

namespace GetCandy\Api\Http\Requests\Utils;

use GetCandy\Api\Http\Requests\FormRequest;

class ProcessImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'email' => 'required|email',
            'file' => 'required|file|mimes:csv,txt',
            'type' => 'in:product,category',
        ];
    }
}
