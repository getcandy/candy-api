<?php

namespace GetCandy\Api\Core\Routes\Actions;

use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Http\Resources\Routes\RouteResource;

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
            'path' => 'string',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Routes\Models\Route|null
     */
    public function handle()
    {
        $query = Route::whereSlug($this->slug);

        if ($this->path) {
            $query->wherePath($this->path);
        }

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
