<?php

namespace GetCandy\Api\Core\Languages\Actions;

use GetCandy\Api\Core\Traits\Actions\AsAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Http\JsonResponse;

class DeleteLanguage
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
        return $this->user->can('manage-languages');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return bool
     */
    public function handle($user, array $attributes = [])
    {
        $this->set('user', $user)->fill($attributes);
        $validatedData = $this->validateAttributes();

        $language = FetchLanguage::run($this->all());

        return $language->delete();
    }

    /**
     * Returns the response from the action.
     *
     * @param   \Lorisleiva\Actions\ActionRequest  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function asController(ActionRequest $request) : JsonResponse
    {
        $this->fillFromRequest($request);
        $result = $this->handle($request->user());

        return $this->respondWithNoContent();
    }
}
