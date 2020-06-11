<?php

namespace GetCandy\Api\Http\Requests\Channels;

use  GetCandy\Api\Core\Channels\Models\Channel;
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
        // return $this->user()->can('create', Channel::class);
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
            'name' => 'required|unique:channels,name',
            'handle' => 'required|unique:channels,handle',
        ];
    }
}
