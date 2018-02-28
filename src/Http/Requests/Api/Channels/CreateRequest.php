<?php

namespace GetCandy\Api\Http\Requests\Channels;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Channels\Models\Channel;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', Channel::class);
        return $this->user()->hasRole('admin');
    }
    public function rules()
    {
        return [
            'name' => 'required|unique:channels,name',
            'handle' => 'required|unique:channels,handle'
        ];
    }
}
