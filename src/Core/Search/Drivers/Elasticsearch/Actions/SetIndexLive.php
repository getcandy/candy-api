<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions;

use Lorisleiva\Actions\Action;

class SetIndexLive extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if (app()->runningInConsole()) {
            return true;
        }

        return $this->user()->can('index-documents');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required',
            'indexes' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return void
     */
    public function handle()
    {
        $client = FetchClient::run();
        dump('Setting live');
        // Indexes
        // $languages = FetchLanguages::run([
        //     'paginate' => false,
        // ]);

        $aliases = [];

        $prefix = config('getcandy.search.index_prefix');

        // Get index names....
        $existing = collect($client->getStatus()->getIndexNames())->filter(function ($indexName) use ($prefix) {
            return strpos($indexName, "{$prefix}_{$this->type}") !== false;
        });

        foreach ($this->indexes as $index) {
            $index->actual->addAlias(
                "{$prefix}_{$this->type}_{$index->language}",
                true
            );
        }

        $indexesToPreserve = collect($this->indexes)->map(function ($index) {
            return $index->actual->getName();
        });

        foreach ($existing as $indexName) {
            $shouldPreserve = $indexesToPreserve->first(function ($index) use ($indexName) {
                return $index == $indexName;
            });

            if (! $shouldPreserve) {
                $index = $client->getIndex($indexName);
                $index->delete();
            }
        }

        // foreach ($languages as $lang) {
        //     // Does the alias exist? if not create it.
        //     if (!$client->getStatus()->aliasExists(
        //         "{$aliasPrefix}_{$lang->lang}"
        //     )) {
        //         dd(1);
        //     }
        //     dd(2);
        // }
    }
}
