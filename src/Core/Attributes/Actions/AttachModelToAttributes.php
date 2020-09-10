<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Foundation\Actions\DecodeIds;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Http\JsonResponse;

class AttachModelToAttributes extends AbstractAction
{
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
            'model' => 'required',
            'attribute_ids' => 'required|array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return bool
     */
    public function handle()
    {
        $ids = DecodeIds::run([
            'model' => Attribute::class,
            'encoded_ids' => $this->attribute_ids,
        ]);

        return $this->model->attributes()->sync($ids);
    }

    /**
     * Returns the response from the action.
     *
     * @param   bool $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  JsonResponse
     */
    public function response($result, $request)
    {
        return $this->respondWithNotContent();
    }
}
