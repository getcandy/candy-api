<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Core\Attributes\Actions\FetchAttributeGroup;

class DeleteAttributeGroup extends AbstractAction
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
            'encoded_id' => 'string|hashid_is_valid:'.AttributeGroup::class.'|required_without:id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Attributes\Models\AttributeGroup|null
     */
    public function handle()
    {
        $group = $this->delegateTo(FetchAttributeGroup::class);
        return $group->delete();
    }

    /**
     * Returns the response from the action.
     *
     * @param   boolean $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (!$result) {
            return $this->errorUnprocessable('Unable to delete attribute group');
        }
        return $this->respondWithNoContent();
    }
}
