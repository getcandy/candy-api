<?php

namespace GetCandy\Api\Core\Countries\Actions;

use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Core\Countries\Resources\CountryCollection;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchCountries extends AbstractAction
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
            'show_disabled' => 'boolean',
            'only_preferred' => 'boolean',
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

        $query = Country::with($includes);

        if (! $this->show_disabled) {
            $query = $query->enabled();
        }

        if ($this->only_preferred) {
            $query = $query->preferred();
        }

        if (! $this->paginate) {
            return $query->get();
        }

        return $query->paginate($this->per_page ?? 50);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Countries\Models\Country|Illuminate\Pagination\LengthAwarePaginator  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Countries\Resources\CountryCollection
     */
    public function response($result, $request)
    {
        return new CountryCollection($result);
    }
}
