<?php

namespace GetCandy\Api\Core\Channels\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Channels\Resources\ChannelResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class UpdateChannel extends AbstractAction
{
    /**
     * The address object we want to update.
     *
     * @var \GetCandy\Api\Core\Channels\Models\Channel
     */
    protected $channel;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->channel = FetchChannel::run([
            'encoded_id' => $this->encoded_id,
        ]);

        return $this->user()->can('update', $this->channel);
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => "unique:channels,name,{$this->channel->id}",
            'handle' => "unique:channels,handle,{$this->channel->id}",
            'default' => 'boolean|in:true,1',
            'url' => 'string',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel
     */
    public function handle(): Channel
    {
        $this->channel->update($this->validated());

        return $this->channel;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Channels\Models\Channel  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Channels\Resources\ChannelResource
     */
    public function response($result, $request): ChannelResource
    {
        return new ChannelResource($result);
    }
}
