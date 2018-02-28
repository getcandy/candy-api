<?php

namespace GetCandy\Api\Http\Requests\Channels;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Channels\Models\Channel;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('delete', Channel::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
        ];
    }
}
