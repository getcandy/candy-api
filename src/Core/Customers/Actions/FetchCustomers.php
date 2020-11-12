<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Resources\CustomerCollection;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchCustomers extends AbstractAction
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
            'keywords' => 'nullable|string',
            'paginate' => 'boolean',
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

        $query = Customer::with($includes);

        if ($this->keywords) {
            $query->where(function ($query) {
                $query->where('firstname', 'LIKE', "%{$this->keywords}%")
                    ->orWhere('lastname', 'LIKE', "%{$this->keywords}%");
            })->orWhere('company_name', 'LIKE', "%{$this->keywords}%");
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
     * @param   \GetCandy\Api\Core\Customers\Models\Customer|Illuminate\Pagination\LengthAwarePaginator  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Customers\Resources\CustomerCollection
     */
    public function response($result, $request): CustomerCollection
    {
        return new CustomerCollection($result);
    }
}
