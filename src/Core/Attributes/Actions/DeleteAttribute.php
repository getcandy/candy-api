<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use Illuminate\Support\Arr;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Core\Attributes\Actions\FetchAttribute;
use GetCandy\Api\Core\Attributes\Resources\AttributeResource;

class DeleteAttribute extends AbstractAction
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
            'id' => 'integer|required_without:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:'.Attribute::class.'|required_without:id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Attributes\Models\Attribute|null
     */
    public function handle()
    {
        $attribute = $this->delegateTo(FetchAttribute::class);
        if ($attribute->system) {
            return false;
        }
        return Attribute::destroy($this->id);
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
        return $this->respondWithNoContent();
    }
}
