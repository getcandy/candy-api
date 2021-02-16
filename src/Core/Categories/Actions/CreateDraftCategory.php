<?php

namespace GetCandy\Api\Core\Categories\Actions;

use Drafting;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Http\Resources\Categories\CategoryResource;

class CreateDraftCategory extends AbstractAction
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
    public function rules(): array
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return void
     */
    public function handle($category)
    {
        $realId = (new Category)->decodeId($category);
        $category = Category::find($realId);

        if (! $category) {
            return null;
        }

        $draft = Drafting::with('categories')->firstOrCreate($category);

        return $draft->load($this->resolveEagerRelations());
    }

    public function response($response, $request)
    {
        if (! $response) {
            return $this->errorNotFound();
        }

        return new CategoryResource($response);
    }
}
