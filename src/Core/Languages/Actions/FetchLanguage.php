<?php

namespace GetCandy\Api\Core\Languages\Actions;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Languages\Resources\LanguageResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FetchLanguage extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * The fetched address model.
     *
     * @var \GetCandy\Api\Core\Languages\Models\Language
     */
    protected $language;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->encoded_id && ! $this->handle) {
            $this->id = (new Language)->decodeId($this->encoded_id);
        }

        try {
            $this->language = Language::with($this->resolveEagerRelations())
                ->withCount($this->resolveRelationCounts())
                ->findOrFail($this->id);
        } catch (ModelNotFoundException $e) {
            if (! $this->runningAs('controller')) {
                throw $e;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'integer|required_without:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:'.Language::class.'|required_without:id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Languages\Models\Language|null
     */
    public function handle()
    {
        return $this->language;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Languages\Models\Language  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Languages\Resources\LanguageResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new LanguageResource($result);
    }
}
