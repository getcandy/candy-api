<?php

namespace GetCandy\Api\Core\Search\Actions;

use GetCandy\Api\Core\Search\SearchManager;
use Lorisleiva\Actions\Action;

class IndexObjects extends Action
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
            'documents' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return void
     */
    public function handle(SearchManager $manager)
    {
        $driver = $manager->with(config('getcandy.search.driver'));

        $driver->update($this->documents);
    }
}
