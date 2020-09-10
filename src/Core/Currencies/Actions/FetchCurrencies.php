<?php

namespace GetCandy\Api\Core\Currencies\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Currencies\Resources\CurrencyCollection;

class FetchCurrencies extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->paginate = $this->paginate === null ?: $this->paginate;

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
            'per_page' => 'numeric|max:200',
            'paginate' => 'boolean',
            'search' => 'nullable|array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $includes = $this->resolveEagerRelations();

        $query = Currency::with($includes);

        if ($this->search) {
            $query = $this->compileSearchQuery($query, $this->search);
        }

        if (! $this->paginate) {
            return $query->get();
        }

        return $query->withCount(
                $this->resolveRelationCounts()
            )->paginate($this->per_page ?? 50);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Currencies\Models\Currency|Illuminate\Pagination\LengthAwarePaginator  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Currencies\Resources\CurrencyCollection
     */
    public function response($result, $request)
    {
        return new CurrencyCollection($result);
    }
}
