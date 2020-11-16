<?php

namespace GetCandy\Api\Core\Search\Actions;

use Lorisleiva\Actions\Action;

class IndexDocuments extends Action
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

        return $this->user()->can('index-products');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'documents' => 'required',
            'driver' => 'required',
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
        $this->timestamp = microtime(true);

        $this->driver->onReference($this->uuid)
            ->index(
                $this->documents,
                $this->final
            );

        // $aliases = $this->getNewAliases(new ProductIndexable, 'products');

        // $indiceNames = GetIndiceNamesAction::run([
        //     'filter' => $this->getNewIndexName()
        // ]);

        // foreach ($this->products as $product) {
        //     $documents = (new ProductIndexable($product))
        //         ->setIndexName($this->getNewIndexName())
        //         ->setSuffix($this->timestamp)
        //         ->getDocuments();
        //     dd($documents);
        // }
        // dd($this->products);
    }
}
