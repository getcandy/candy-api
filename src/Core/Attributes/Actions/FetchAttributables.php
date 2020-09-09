<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Resources\AttributeCollection;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Support\Collection;

class FetchAttributables extends AbstractAction
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
    public function rules()
    {
        return [
            'type' => 'string|required',
            'encoded_ids' => 'array|required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Support\Collection
     */
    public function handle()
    {
        $ids = [];
        foreach ($this->encoded_ids as $hash) {
            $ids[] = DecodeId::run([
                'model' => $this->type,
                'encoded_id' => $hash
            ]);
        }
        $query = Attribute::with(['attributables', 'attributables.records']);

        if ($this->type) {
            $query = Attribute::with(['attributables' => function ($query) {
                $query->where('attributable_type', '=', $this->type);
            }, 'attributables.records']);
        }

        return $query->find($ids);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \Illuminate\Support\Collection  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Attributes\Resources\AttributeCollection|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new AttributeCollection($result);
    }
}
