<?php

namespace GetCandy\Api\Http\Requests\Channels;

use GetCandy\Api\Channels\Models\Channel;
use GetCandy\Api\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(Channel $channel)
    {
        return [
            'name' => 'unique:channels,name,'.$channel->decodeId($this->channel),
            'handle' => 'unique:channels,handle,'.$channel->decodeId($this->channel),
        ];
    }
}
