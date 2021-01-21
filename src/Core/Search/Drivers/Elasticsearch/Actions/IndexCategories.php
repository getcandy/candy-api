<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Mapping;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;
use GetCandy\Api\Core\Languages\Actions\FetchLanguages;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Events\IndexingCompleteEvent;
use Lorisleiva\Actions\Action;

class IndexCategories extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
//
//        if (app()->runningInConsole()) {
//            return true;
//        }
//        return $this->user()->can('index-documents');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'categories' => 'required',
            'uuid' => 'required',
            'final' => 'boolean',
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

        $languages = FetchLanguages::run([
            'paginate' => false,
        ])->pluck('lang');

        $customerGroups = FetchCustomerGroups::run([
            'paginate' => false,
        ]);

        $indexes = FetchIndex::run([
            'languages' => $languages->toArray(),
            'type' => 'categories',
            'uuid' => $this->uuid,
        ]);

        $documents = [];

        foreach ($this->categories as $category) {
            $indexables = FetchCategoryDocument::run([
                'model' => $category,
                'customer_groups' => $customerGroups,
            ]);

            foreach ($indexables as $document) {
                $documents[$document->lang][] = $document;
            }
        }

        foreach ($indexes as $index) {
            // If the index doesn't exist, then we update the mapping
            if (empty($index->actual->getMapping())) {
                $mapping = new Mapping();
                $mapping->setProperties(
                    FetchCategoryMapping::run()
                );
                $mapping->send($index->actual);
            }
            // Get the documents for the index language.
            $docs = collect($documents[$index->language] ?? [])->map(function ($document) {
                return new Document($document->getId(), $document->getData());
            });

            $bulk = new Bulk($client);
            $bulk->setIndex($index->actual);
            $bulk->addDocuments($docs->toArray());
            $bulk->send();
        }

        if ($this->final) {
            event(new IndexingCompleteEvent($indexes, 'categories'));
        }
    }
}
