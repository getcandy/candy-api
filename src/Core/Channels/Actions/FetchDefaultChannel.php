<?php

namespace GetCandy\Api\Core\Channels\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Channels\Resources\ChannelResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchDefaultChannel extends AbstractAction
{
    /**
     * The fetched address model.
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
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel
     */
    public function handle()
    {
        return Channel::with($this->resolveEagerRelations())->whereDefault(true)->first();
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Addresses\Models\Address  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Channels\Resources\ChannelResource
     */
    public function response($result, $request): ChannelResource
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new ChannelResource($result);
    }
}
