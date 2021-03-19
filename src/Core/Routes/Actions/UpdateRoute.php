<?php

namespace GetCandy\Api\Core\Routes\Actions;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Routes\Resources\RouteResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Facades\DB;

class UpdateRoute extends AbstractAction
{
    protected $route;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-routes');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->route = FetchRoute::run([
            'encoded_id' => $this->encoded_id,
            'draft' => true,
        ]);

        return [
            'path' => 'nullable',
            'slug' => [
                'required',
                function ($attribute, $value, $fail) {
                    $ids = [
                        $this->route->id,
                    ];
                    if ($this->route->publishedParent) {
                        $ids[] = $this->route->publishedParent->id;
                    }
                    $result = DB::table('routes')->whereElementType($this->route->element_type)->whereSlug($value)->whereNotIn('id', $ids)->exists();
                    if ($result) {
                        $fail('This slug has already been taken for this element type');
                    }
                },
            ],
            'language_id' => 'nullable|string|hashid_is_valid:'.Language::class,
            'description' => 'nullable|string',
            'default' => 'boolean',
            'redirect' => 'boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Routes\Models\Route
     */
    public function handle()
    {
        $attributes = $this->validated();
        $attributes['language_id'] = (new Language)->decodeId($this->language_id);
        $this->route->update($attributes);

        if ($this->route->default) {
            // Need to make sure we unset any defaults of any siblings
            // as we can only have one
            Route::whereElementType($this->route->element_type)
                ->whereElementId($this->route->element_id)
                ->where('id', '!=', $this->route->id)
                ->update([
                    'default' => false,
                ]);
        }

        return $this->route;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Routes\Models\Route  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Routes\Resources\RouteResource
     */
    public function response($result, $request)
    {
        return new RouteResource($result);
    }
}
