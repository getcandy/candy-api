<?php

namespace GetCandy\Api\Core\Products\Actions;

use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Core\Products\Resources\ProductFamilyResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FetchProductFamily extends AbstractAction
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
            'id' => 'integer|required_without:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:'.ProductFamily::class.'|required_without:id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Products\Models\ProductFamily|null
     */
    public function handle()
    {
        if ($this->encoded_id && ! $this->handle) {
            $this->id = (new ProductFamily)->decodeId($this->encoded_id);
        }

        try {
            return Productfamily::with($this->resolveEagerRelations())
                ->withCount($this->resolveRelationCounts())
                ->findOrFail($this->id);
        } catch (ModelNotFoundException $e) {
            if (! $this->runningAs('controller')) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Products\Models\ProductFamily|null  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Products\Resources\ProductFamilyResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new ProductFamilyResource($result);
    }
}
