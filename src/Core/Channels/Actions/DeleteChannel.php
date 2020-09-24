<?php

namespace GetCandy\Api\Core\Channels\Actions;

use GetCandy\Api\Core\Exceptions\DefaultRecordRequiredException;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Http\JsonResponse;

class DeleteChannel extends AbstractAction
{
    use ReturnsJsonResponses;

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

        return $this->user()->can('delete', $this->channel);
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
     * @return bool
     */
    public function handle()
    {
        if ($this->channel->default) {
            if (! $this->runningAs('controller')) {
                throw new DefaultRecordRequiredException;
            }

            return false;
        }

        return $this->channel->delete();
    }

    /**
     * Returns the response from the action.
     *
     * @param   bool $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function response($result, $request): JsonResponse
    {
        if (! $result) {
            return $this->errorUnprocessable('You cannot remove the default record.');
        }

        return $this->respondWithNoContent();
    }
}
