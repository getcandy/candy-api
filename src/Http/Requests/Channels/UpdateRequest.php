<?php

namespace GetCandy\Api\Http\Requests\Channels;

use GetCandy\Api\Core\Channels\Models\Channel;
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
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Channel $channel)
    {
        return [
            'name' => 'unique:channels,name,'.$channel->decodeId($this->channel),
            'handle' => 'unique:channels,handle,'.$channel->decodeId($this->channel),
        ];
    }
}
