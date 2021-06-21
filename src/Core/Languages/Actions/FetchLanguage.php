<?php

namespace GetCandy\Api\Core\Languages\Actions;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Languages\Resources\LanguageResource;
use GetCandy\Api\Core\Traits\Actions\AsAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Lorisleiva\Actions\ActionRequest;

class FetchLanguage
{
    use AsAction;
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
    public function handle($attributes = [])
    {
        $this->fill($attributes);

        if ($this->encoded_id) {
            $this->id = (new Language)->decodeId($this->encoded_id);
        }

        $this->language = Language::with($this->resolveEagerRelations())
            ->withCount($this->resolveRelationCounts())
            ->findOrFail($this->id);

        return $this->language;
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
        $result = $this->handle();

        return new LanguageResource($result);
    }
}
