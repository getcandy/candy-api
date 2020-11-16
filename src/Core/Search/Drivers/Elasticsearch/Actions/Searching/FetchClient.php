<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use Elastica\Client;
use Lorisleiva\Actions\Action;

class FetchClient extends Action
{
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
    public function rules()
    {
        return [
            'config' => 'nullable|array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Elastica\Client
     */
    public function handle()
    {
        return new Client(config('getcandy.search.client_config.elastic', $this->config ?: []), null, null);
    }
}
