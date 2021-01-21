<?php

namespace GetCandy\Api\Core\Routes\Actions;

use GetCandy\Api\Core\Routes\Resources\RouteResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Support\Arr;

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
        return [
            'slug' => 'required|unique_with:routes,path,'.$this->path,
            'path' => 'unique_with:routes,slug,'.$this->slug,
            'element' => 'required',
            'lang' => 'required|string',
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
        $this->element->routes()->create(
            Arr::except($this->validated(), ['element']),
        );

        return $this->element;
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
