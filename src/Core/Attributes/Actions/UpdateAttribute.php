<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Core\Attributes\Resources\AttributeResource;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Support\Arr;

class UpdateAttribute extends AbstractAction
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
        $attributeId = DecodeId::run([
            'encoded_id' => $this->encoded_id,
            'model' => Attribute::class,
        ]);

        return [
            'attribute_group_id' => 'nullable|hashid_is_valid:'.AttributeGroup::class,
            'name' => 'nullable|array',
            'handle' => 'string|nullable|unique:attributes,handle,'.$attributeId,
            'variant' => 'boolean',
            'searchable' => 'boolean',
            'filterable' => 'boolean',
            'system' => 'boolean',
            'channeled' => 'boolean',
            'translatable' => 'boolean',
            'scopeable' => 'boolean',
            'required' => 'boolean',
            'type' => 'string',
            'lookups' => 'array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Attributes\Models\Attribute|null
     */
    public function handle()
    {
        $attribute = (new FetchAttribute)->actingAs($this->user())->run([
            'encoded_id' => $this->encoded_id,
        ]);

        $data = Arr::except($this->validated(), ['attribute_group_id']);

        if ($this->attribute_group_id) {
            $data['attribute_group_id'] = DecodeId::run([
                'encoded_id' => $this->attribute_group_id,
                'model' => AttributeGroup::class,
            ]);
        }
        $attribute->update($data);

        return $attribute;
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
