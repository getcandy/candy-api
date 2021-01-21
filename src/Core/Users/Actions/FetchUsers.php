<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy;
use GetCandy\Api\Core\Users\Resources\UserCollection;
use Lorisleiva\Actions\Action;

class FetchUsers extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->paginate = $this->paginate === null ?: $this->paginate;

        return $this->user()->can('view-users');
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
            'keywords' => 'nullable|string',
            'ids' => 'array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $userModel = GetCandy::getUserModel();

        $query = (new $userModel)->with(['customer']);
        if ($this->keywords) {
            $keywords = explode(' ', $this->keywords);
            foreach ($keywords as $keyword) {
                $query = $query->whereHas('customer', function ($innerQuery) use ($keyword) {
                    $innerQuery->where('firstname', 'LIKE', '%'.$keyword.'%')
                        ->orWhere('lastname', 'LIKE', '%'.$keyword.'%')
                        ->orWhere('company_name', 'LIKE', '%'.$keyword.'%')
                        ->orWhere('email', 'LIKE', '%'.$keyword.'%');
                });
            }
        }

        if (! empty($this->ids)) {
            $realIds = collect($this->ids)->map(function ($id) use ($userModel) {
                return $userModel->decodeId($id);
            })->toArray();
            $query = $query->whereIn('id', $realIds);
        }

        if (! $this->paginate) {
            return $query->get();
        }

        return $query->paginate($this->per_page ?? 50);
    }

    /**
     * Returns the response from the action.
     *
     * @param $result
     * @param \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Core\Users\Resources\UserCollection
     */
    public function response($result, $request)
    {
        return new UserCollection($result);
    }
}
