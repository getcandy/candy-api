<?php

namespace GetCandy\Api\Core\Search\Actions;

use GetCandy\Api\Core\Foundation\Actions\DecodeIds;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Lorisleiva\Actions\Action;

class FetchSearchedIds extends AbstractAction
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
            'model' => 'required',
            'encoded_ids' => 'array|min:0',
            'include' => 'nullable',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function handle()
    {
        $model = new $this->model;
        $parsedIds = $this->delegateTo(DecodeIds::class);
        $placeholders = implode(',', array_fill(0, count($parsedIds), '?')); // string for the query

        $query = $model->with($this->resolveEagerRelations())->whereIn("{$model->getTable()}.id", $parsedIds);

        if (count($parsedIds)) {
            $query = $query->orderByRaw("field({$model->getTable()}.id,{$placeholders})", $parsedIds);
        }

        return $query->get();
    }
}
