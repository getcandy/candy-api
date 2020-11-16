<?php

namespace GetCandy\Api\Core\Attributes\Actions;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchAttributes extends AbstractAction
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
    public function rules()
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Attributes\Models\Attribute
     */
    public function handle()
    {
        return Attribute::get();
    }
}
