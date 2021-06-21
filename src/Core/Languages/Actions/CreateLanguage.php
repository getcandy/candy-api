<?php

namespace GetCandy\Api\Core\Languages\Actions;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Languages\Resources\LanguageResource;
use GetCandy\Api\Core\Traits\Actions\AsAction;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Http\Response;

class CreateLanguage
{
    use AsAction;

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
        return [
            'code' => [
                'required',
                'string',
                'unique:languages,code',
                // See https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
                'regex:/^[a-zA-Z0-9-]*$/',
            ],
            'name' => 'required|string',
            'default' => 'boolean',
            'enabled' => 'boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Languages\Models\Language
     */
    public function handle($user, array $attributes = []) : Language
    {
        $this->set('user', $user)->fill($attributes);
        $validatedData = $this->validateAttributes();

        $language = Language::create($validatedData);

        return $language->load($this->resolveEagerRelations());
    }

    /**
     * Returns the response from the action.
     *
     * @param   \Lorisleiva\Actions\ActionRequest  $request
     *
     * @return  \GetCandy\Api\Core\Languages\Resources\LanguageResource
     */
    public function asController(ActionRequest $request) : LanguageResource
    {
        $this->fillFromRequest($request);
        $result = $this->handle($request->user());

        return new LanguageResource($result);
    }
}
