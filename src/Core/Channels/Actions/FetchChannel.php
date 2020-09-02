<?php

namespace GetCandy\Api\Core\Channels\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Channels\Resources\ChannelResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FetchChannel extends AbstractAction
{
    use ReturnsJsonResponses;

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
        if ($this->encoded_id && ! $this->handle) {
            $this->id = (new Channel)->decodeId($this->encoded_id);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'integer|required_without_all:encoded_id,handle',
            'encoded_id' => 'string|hashid_is_valid:'.Channel::class.'|required_without_all:id,handle',
            'handle' => 'string|required_without_all:encoded_id,id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel|null
     */
    public function handle()
    {
        try {
            $query = Channel::with($this->resolveEagerRelations());
            if ($this->handle) {
                return $query->whereHandle($this->handle)->firstOrFail();
            }

            return $query->findOrFail($this->id);
        } catch (ModelNotFoundException $e) {
            if ($this->runningAs('controller')) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Addresses\Models\Address  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Channels\Resources\ChannelResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new ChannelResource($result);
    }
}
