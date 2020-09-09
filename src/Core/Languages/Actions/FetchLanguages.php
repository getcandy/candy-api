<?php

namespace GetCandy\Api\Core\Languages\Actions;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Languages\Resources\LanguageCollection;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchLanguages extends AbstractAction
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

        $query = Language::with($includes);

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
     * @param   \GetCandy\Api\Core\Languages\Models\Language|Illuminate\Pagination\LengthAwarePaginator  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Languages\Resources\LanguageCollection
     */
    public function response($result, $request)
    {
        return new LanguageCollection($result);
    }
}
