<?php

namespace GetCandy\Api\Core\Categories\Actions;

use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class RebuildTree extends AbstractAction
{
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
    public function handle()
    {
        // Get all out categories and map them into a tree.
        $categories = Category::whereParentId(null)->get()->map(function ($category) {
            return $this->map($category);
        });

        // Pass false as we don't want to delete any drafts.
        Category::rebuildTree($categories->toArray(), false);
    }

    protected function map($category)
    {
        return [
            'id' => $category->id,
            'children' => $category->children->map(function ($category) {
                return $this->map($category);
            })->toArray(),
        ];
    }
}
