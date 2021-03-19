<?php

namespace GetCandy\Api\Core\Routes\Actions;

use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\AliasResolver;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\Routes\Resources\RouteResource;

class SearchForRoute extends AbstractAction
{
    use ReturnsJsonResponses;

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
    public function rules(): array
    {
        return [
            'slug' => 'required|string',
            'element_type' => 'required|string',
            'language_id' => 'required|string|hashid_is_valid:'.Language::class,
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Routes\Models\Route|null
     */
    public function handle()
    {
        $elementType = AliasResolver::resolve($this->element_type);
        $languageId = (new Language)->decodeId($this->language_id);

        $query = Route::whereSlug($this->slug)->with(
            $this->resolveEagerRelations()
        )->whereElementType($elementType)
        ->whereLanguageId($languageId)
        ->withCount(
            $this->resolveRelationCounts()
        );

        return $query->first();
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Routes\Models\Route  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Http\Resources\Routes\RouteResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new RouteResource($result);
    }
}
