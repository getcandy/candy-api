<?php

namespace GetCandy\Api\Core\Routes\Actions;

use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Routes\Resources\RouteResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class UpdateRoute extends AbstractAction
{
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
        $routeId = DecodeId::run([
            'encoded_id' => $this->encoded_id,
            'model' => Route::class,
        ]);

        return [
            'slug' => 'required|unique_with:routes,path,'.$this->path.','.$routeId,
            'path' => 'unique_with:routes,slug,'.$this->slug.','.$routeId,
            'lang' => 'nullable|string',
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
        $route = $this->delegateTo(FetchRoute::class);
        $route->update($this->validated());

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
