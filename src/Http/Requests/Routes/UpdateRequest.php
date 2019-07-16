<?php

namespace GetCandy\Api\Http\Requests\Routes;

use GetCandy\Api\Core\Routes\Models\Route;
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
        // return $this->user()->can('create', Product::class);
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Route $route)
    {
        return [
            'slug' => 'required|unique:routes,slug,'.$route->decodeId($this->route),
        ];
    }
}
