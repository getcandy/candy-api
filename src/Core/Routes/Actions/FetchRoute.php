<?php

namespace GetCandy\Api\Core\Routes\Actions;

use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Http\Resources\Routes\RouteResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FetchRoute extends AbstractAction
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
            'id' => 'integer|required_without_all:encoded_id,search',
            'encoded_id' => 'string|hashid_is_valid:'.Route::class.'|required_without_all:id,search',
            'search' => 'required_without_all:encoded_id,id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Routes\Models\Route|null
     */
    public function handle()
    {
        if ($this->encoded_id) {
            $this->id = (new Route)->decodeId($this->encoded_id);
        }

        if (! $this->id) {
            $query = Route::getQuery();

            return $this->compileSearchQuery($query, $this->search)->first();
        }

        try {
            return Route::with($this->resolveEagerRelations())
                ->withCount($this->resolveRelationCounts())
                ->findOrFail($this->id);
        } catch (ModelNotFoundException $e) {
            if (! $this->runningAs('controller')) {
                throw $e;
            }
        }

        return null;
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
