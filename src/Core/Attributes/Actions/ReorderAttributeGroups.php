<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\Foundation\Actions\DecodeIds;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;

class ReorderAttributeGroups extends AbstractAction
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
            'ordering' => 'required|array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return boolean
     */
    public function handle()
    {
        $parsed = [];

        foreach ($this->ordering as $group) {
            $decodedId = DecodeId::run([
                'model' => AttributeGroup::class,
                'encoded_id' => $group['id'],
            ]);
            $parsed[$decodedId] = $group['position'];
        }

        $groups = (new FetchAttributeGroups)->actingAs($this->user())
            ->run([
                'paginate' => false,
                'search' => [
                    'id' => DecodeIds::run([
                        'model' => AttributeGroup::class,
                        'encoded_ids' => collect($this->ordering)->map(function ($grp) {
                            return $grp['id'];
                        })->toArray()
                    ])
                ]
            ]);

        foreach ($groups as $group) {
            $group->update([
                'position' => $parsed[$group->id]
            ]);
        }

        return true;
    }

    /**
     * Returns the response from the action.
     *
     * @param   boolean  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        return $this->respondWithNoContent();
    }
}
