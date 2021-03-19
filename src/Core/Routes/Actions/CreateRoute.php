<?php

namespace GetCandy\Api\Core\Routes\Actions;

use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\AliasResolver;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\Routes\Resources\RouteResource;

class CreateRoute extends AbstractAction
{
    use ReturnsJsonResponses;

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
    public function rules()
    {
        $this->set('element_type', AliasResolver::resolve($this->element_type));

        return [
            'slug' => 'required|unique_with:routes,element_type,'.$this->element_type,
            'element_type' => [
                'required',
                'string',
                function () {
                    return class_exists($this->element_type);
                },
            ],
            'element_id' => [
                'required',
                'string',
                function () {
                    return (new $this->element_type)->decodeId($this->element_id);
                },
            ],
            'language_id' => 'required|string|hashid_is_valid:'.Language::class,
            'default' => 'boolean',
            'redirect' => 'boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return bool
     */
    public function handle()
    {
        $elementId = (new $this->element_type)->decodeId($this->element_id);
        $languageId = (new Language)->decodeId($this->language_id);

        $route = Route::create([
            'element_id' => $elementId,
            'element_type' => $this->element_type,
            'default' => $this->default,
            'redirect' => $this->redirect,
            'language_id' => $languageId,
            'slug' => $this->slug,
        ]);

        if ($route->default) {
            // Need to make sure we unset any defaults of any siblings
            // as we can only have one
            Route::whereElementType($route->element_type)
                ->whereElementId($route->element_id)
                ->where('id', '!=', $route->id)
                ->where('language_id', '=', $route->language_id)
                ->update([
                    'default' => false,
                ]);
        }

        return $route;
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
