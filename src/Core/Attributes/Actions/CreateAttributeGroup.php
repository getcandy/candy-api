<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use Illuminate\Support\Arr;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Core\Attributes\Resources\AttributeGroupResource;

class CreateAttributeGroup extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-attributes');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|array',
            'handle' => 'string|required|unique:attribute_groups,handle',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Attributes\Models\AttributeGroup|null
     */
    public function handle()
    {
        $group = new AttributeGroup(
            Arr::except($this->validated(), ['attribute_group_id'])
        );
        
        $group->position = AttributeGroup::count() + 1;
        $group->save();

        return $group;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Attributes\Models\AttributeGroup  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Attributes\Resources\AttributeGroupResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        return new AttributeGroupResource($result);
    }
}
