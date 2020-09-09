<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Core\Attributes\Resources\AttributeGroupResource;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Support\Arr;

class UpdateAttributeGroup extends AbstractAction
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
        $groupId = DecodeId::run([
            'encoded_id' => $this->encoded_id,
            'model' => AttributeGroup::class,
        ]);

        return [
            'encoded_id' => 'required|hashid_is_valid:'.AttributeGroup::class,
            'name' => 'nullable|array',
            'handle' => 'string|nullable|unique:attribute_groups,handle,'.$groupId,
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Attributes\Models\Attribute|null
     */
    public function handle()
    {
        $group = (new FetchAttributeGroup)->actingAs($this->user())->run([
            'encoded_id' => $this->encoded_id,
        ]);

        $data = Arr::except($this->validated(), ['encoded_id']);

        $group->update($data);

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
        if (! $result) {
            return $this->errorNotFound();
        }

        return new AttributeGroupResource($result);
    }
}
