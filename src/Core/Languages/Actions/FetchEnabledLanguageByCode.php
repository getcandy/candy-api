<?php

namespace GetCandy\Api\Core\Languages\Actions;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Languages\Resources\LanguageResource;
use GetCandy\Api\Core\Traits\Actions\AsAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;

class FetchEnabledLanguageByCode
{
    use AsAction;
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
            'code' => 'required|string',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Languages\Models\Language|null
     */
    public function handle($attributes = [])
    {
        $this->fill($attributes);

        return Language::enabled()->code($this->code)->first();
    }

    /**
     * Returns the response from the action.
     *
     * @param   \Lorisleiva\Actions\ActionRequest  $request
     *
     * @return  \GetCandy\Api\Core\Languages\Resources\LanguageResource|\Illuminate\Http\JsonResponse
     */
    public function asController(ActionRequest $request)
    {
        $this->fillFromRequest($request);
        $result = $this->handle();

        if (! $result) {
            return $this->errorNotFound();
        }

        return new LanguageResource($result);
    }
}
