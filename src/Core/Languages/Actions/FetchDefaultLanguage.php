<?php

namespace GetCandy\Api\Core\Languages\Actions;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Languages\Resources\LanguageResource;
use GetCandy\Api\Core\Traits\Actions\AsAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;

class FetchDefaultLanguage
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
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Languages\Models\Language|null
     */
    public function handle()
    {
        return Language::default()->first();
    }
}
