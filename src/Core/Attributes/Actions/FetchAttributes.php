<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\Attributes\Resources\AttributeCollection;

class FetchAttributes extends AbstractAction
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
    public function rules(): array
    {
        return [
            'per_page' => 'numeric|max:200',
            'paginate' => 'boolean',
        ];
    }

    public function afterValidator($validator)
    {
        $this->set('paginate', $this->paginate === null ?: $this->paginate);
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel|null
     */
    public function handle()
    {
        $includes = $this->resolveEagerRelations();

        if (! $this->paginate) {
            return Attribute::with($includes)->get();
        }

        return Attribute::with($includes)->paginate($this->per_page ?? 50);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Attributes\Models\Attribute  $result
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
