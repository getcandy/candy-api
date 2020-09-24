<?php

namespace GetCandy\Api\Core\Channels\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Channels\Resources\ChannelResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class CreateChannel extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-channels');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|unique:channels,name',
            'handle' => 'required|unique:channels,handle',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel
     */
    public function handle(): Channel
    {
        $channel = Channel::create($this->validated());

        return $channel->load($this->resolveEagerRelations());
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Channels\Models\Channel  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Channels\Resources\ChannelResource
     */
    public function response($result, $request)
    {
        return new ChannelResource($result);
    }
}
