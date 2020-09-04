<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Core\Attributes\Resources\AttributeResource;

class FetchAttribute extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->encoded_id && ! $this->handle) {
            $this->id = (new Attribute)->decodeId($this->encoded_id);
        }

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
            'id' => 'integer|required_without_all:encoded_id,handle',
            'encoded_id' => 'string|hashid_is_valid:'.Attribute::class.'|required_without_all:id,handle',
            'handle' => 'string|required_without_all:encoded_id,id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Attributes\Models\Attribute|null
     */
    public function handle()
    {
        try {
            $query = Attribute::with($this->resolveEagerRelations());
            if ($this->handle) {
                return $query->whereHandle($this->handle)->firstOrFail();
            }

            return $query->findOrFail($this->id);
        } catch (ModelNotFoundException $e) {
            if ($this->runningAs('controller')) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Attributes\Models\Attribute  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Attributes\Resources\AttributeResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new AttributeResource($result);
    }
}
