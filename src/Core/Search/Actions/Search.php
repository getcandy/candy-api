<?php

namespace GetCandy\Api\Core\Search\Actions;

use GetCandy\Api\Core\Search\Contracts\SearchManagerContract;
use Lorisleiva\Actions\Action;

class Search extends Action
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
            'driver' => 'nullable',
            'params' => 'nullable|array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle(SearchManagerContract $search)
    {
        $driver = $search->with($this->driver);

        return $driver->search($this->request ?? $this->params);
    }
}
